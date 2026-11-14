<?php
// POST /api/create-order  { course, name, email, phone }
// The price comes from the server-side catalogue, never from the browser.
// -> { order_id, amount, currency, key_id, course, prefill }

declare(strict_types=1);
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/orders.php';

require_post();
$in = request_data();

$c = course((string)($in['course'] ?? ''));
if (!$c) json_out(400, ['error' => 'Please choose a valid course.']);

[$who, $err] = validate_contact($in);
if ($err) json_out(400, ['error' => $err]);

$amount = (int)$c['price'];
if ($amount < 100) json_out(400, ['error' => 'Minimum amount is ₹1 (100 paise).']);

if (!razorpay_configured()) {
    error_log('Razorpay keys are missing from the settings file');
    json_out(500, ['error' => 'Online payments are not available right now. Please contact us.']);
}

$receipt = 'WP' . date('ymdHis') . strtoupper(bin2hex(random_bytes(2)));
[$status, $order] = razorpay_api('POST', '/orders', [
    'amount'   => $amount,
    'currency' => 'INR',
    'receipt'  => $receipt,
    'notes'    => ['course' => $c['slug'], 'name' => $who['name'], 'email' => $who['email'], 'phone' => $who['phone']],
]);

if ($status === 401) {
    error_log('Razorpay authentication failed — check RAZORPAY_KEY_ID / RAZORPAY_KEY_SECRET');
    json_out(401, ['error' => 'Payment gateway authentication failed. Please contact us.']);
}
if ($status < 200 || $status >= 300 || empty($order['id'])) {
    error_log('Razorpay create order failed: HTTP ' . $status . ' ' . json_encode($order));
    json_out(500, ['error' => 'Could not start the payment. Please try again in a moment.']);
}

try {
    db()->prepare('INSERT INTO orders (receipt, rzp_order_id, course_slug, amount, currency, name, email, phone, status, created_at)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([$receipt, $order['id'], $c['slug'], $order['amount'], $order['currency'],
                   $who['name'], $who['email'], $who['phone'], 'created', now()]);
} catch (\Throwable $ex) {
    error_log('Saving order failed: ' . $ex->getMessage());
    json_out(500, ['error' => 'Could not start the payment. Please try again in a moment.']);
}

json_out(200, [
    'order_id' => $order['id'],
    'amount'   => $order['amount'],
    'currency' => $order['currency'],
    'key_id'   => env('RAZORPAY_KEY_ID'),     // public key — safe to send to the browser
    'course'   => $c['title'],
    'prefill'  => ['name' => $who['name'], 'email' => $who['email'], 'contact' => $who['phone']],
]);
