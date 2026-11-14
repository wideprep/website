<?php
// Order helpers shared by the checkout, webhook and admin pages.

declare(strict_types=1);

require_once __DIR__ . '/mailer.php';

/** Calls the Razorpay REST API. Returns [httpStatus, decodedBody|null]. */
function razorpay_api(string $method, string $path, ?array $body = null): array {
    $ch = curl_init('https://api.razorpay.com/v1' . $path);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_USERPWD        => env('RAZORPAY_KEY_ID') . ':' . env('RAZORPAY_KEY_SECRET'),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
    ]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $resp = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    if ($resp === false) error_log('Razorpay network error: ' . curl_error($ch));
    curl_close($ch);
    return [$resp === false ? 0 : $status, $resp === false ? null : json_decode($resp, true)];
}

function razorpay_configured(): bool {
    return env('RAZORPAY_KEY_ID') !== '' && env('RAZORPAY_KEY_SECRET') !== '';
}

function find_order_by_rzp_id(string $rzpOrderId): ?array {
    $st = db()->prepare('SELECT * FROM orders WHERE rzp_order_id = ?');
    $st->execute([$rzpOrderId]);
    return $st->fetch() ?: null;
}

/**
 * Marks an order paid (once) and emails the access link (once).
 * Called from both the browser verification and the Razorpay webhook,
 * so it is safe to call more than once for the same order.
 */
function fulfil_order(array $order, string $paymentId): array {
    $pdo = db();
    if ($order['status'] !== 'paid') {
        $pdo->prepare("UPDATE orders SET status = 'paid', rzp_payment_id = ?, paid_at = ?,
                       access_token = COALESCE(access_token, ?) WHERE id = ? AND status <> 'paid'")
            ->execute([$paymentId, now(), bin2hex(random_bytes(16)), $order['id']]);
    }
    $order = find_order_by_rzp_id($order['rzp_order_id']);

    // Claim the email job atomically so the webhook and the browser don't both send it.
    $claim = $pdo->prepare('UPDATE orders SET email_sent_at = ? WHERE id = ? AND email_sent_at IS NULL');
    $claim->execute([now(), $order['id']]);
    if ($claim->rowCount() === 1) {
        if (!send_access_email($order)) {
            $pdo->prepare('UPDATE orders SET email_sent_at = NULL WHERE id = ?')->execute([$order['id']]);
        }
        send_sale_notification($order);
    }
    return find_order_by_rzp_id($order['rzp_order_id']);
}

function thank_you_url(array $order): string {
    return SITE_URL . '/thank-you?t=' . urlencode((string)$order['access_token']);
}

function send_access_email(array $order): bool {
    $c = course($order['course_slug']);
    $title = $c['title'] ?? $order['course_slug'];
    $link = course_access_link($order['course_slug']);
    $page = thank_you_url($order);

    $accessHtml = $link !== ''
        ? '<p><a href="' . e($link) . '" style="display:inline-block;background:#0B4F6C;color:#fff;text-decoration:none;padding:12px 20px;border-radius:8px;font-weight:bold">Join / open your course</a></p>'
          . '<p style="font-size:13px;color:#4a6b7a">If the button doesn’t work, copy this link: ' . e($link) . '</p>'
        : '<p>Your joining link will be emailed to you before the session starts.</p>';

    $html = mail_layout('You’re enrolled in ' . $title . '!',
        '<p>Hi ' . e($order['name']) . ',</p>'
        . '<p>Thank you for your payment of <b>' . e(rupees((int)$order['amount'])) . '</b>. Your seat in <b>' . e($title) . '</b> is confirmed.</p>'
        . $accessHtml
        . '<p><b>Schedule:</b> ' . e($c['schedule'] ?? '') . '</p>'
        . '<p>You can also open your access page any time: <a href="' . e($page) . '">' . e($page) . '</a></p>'
        . '<p style="font-size:13px;color:#4a6b7a">Payment ID: ' . e($order['rzp_payment_id']) . ' · Order: ' . e($order['receipt']) . '<br>'
        . 'Please keep this link private — it is for your use only.</p>'
        . '<p>Questions? Just reply to this email.</p>');

    $text = "Hi {$order['name']},\n\nThank you for your payment of " . rupees((int)$order['amount'])
        . ". Your seat in $title is confirmed.\n\n"
        . ($link !== '' ? "Your access link: $link\n" : "Your joining link will be emailed to you before the session starts.\n")
        . "Schedule: " . ($c['schedule'] ?? '') . "\nAccess page: $page\n\n"
        . "Payment ID: {$order['rzp_payment_id']}  Order: {$order['receipt']}\n\n— WidePrep (" . BUSINESS['email'] . ")";

    return send_mail($order['email'], $order['name'], 'Your WidePrep access: ' . $title, $html, $text, BUSINESS['email']);
}

function send_sale_notification(array $order): void {
    $c = course($order['course_slug']);
    $to = env('MAIL_NOTIFY', BUSINESS['email']);
    $rows = [
        'Course' => $c['title'] ?? $order['course_slug'], 'Amount' => rupees((int)$order['amount']),
        'Name' => $order['name'], 'Email' => $order['email'], 'Phone' => $order['phone'],
        'Payment ID' => $order['rzp_payment_id'], 'Receipt' => $order['receipt'],
    ];
    $html = '';
    $text = '';
    foreach ($rows as $k => $v) { $html .= '<b>' . e($k) . ':</b> ' . e((string)$v) . '<br>'; $text .= "$k: $v\n"; }
    send_mail($to, 'WidePrep', 'New enrolment: ' . ($c['title'] ?? '') . ' — ' . $order['name'],
        mail_layout('New enrolment', $html), $text, $order['email']);
}
