<?php
// POST /api/razorpay-webhook — called by Razorpay's servers (not the browser).
// Backup path: if a buyer closes the tab before the browser verification runs,
// this still marks the order paid and emails the access link.
// Set it up in Razorpay Dashboard → Webhooks with events payment.captured and order.paid.

declare(strict_types=1);
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/orders.php';

require_post();
$secret = env('RAZORPAY_WEBHOOK_SECRET');
$raw = file_get_contents('php://input') ?: '';
$sig = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

if ($secret === '') { error_log('Webhook received but RAZORPAY_WEBHOOK_SECRET is not set'); json_out(500, ['ok' => false]); }
if ($sig === '' || !hash_equals(hash_hmac('sha256', $raw, $secret), $sig)) {
    error_log('Razorpay webhook signature mismatch');
    json_out(400, ['ok' => false]);
}

$event = json_decode($raw, true) ?: [];
$payment = $event['payload']['payment']['entity'] ?? null;
if (!in_array($event['event'] ?? '', ['payment.captured', 'order.paid'], true) || !$payment) {
    json_out(200, ['ok' => true, 'ignored' => true]);
}

$order = find_order_by_rzp_id((string)($payment['order_id'] ?? ''));
if (!$order) json_out(200, ['ok' => true, 'unknown_order' => true]);
if ((int)$payment['amount'] !== (int)$order['amount']) {
    error_log("Webhook amount mismatch for {$order['rzp_order_id']}");
    json_out(200, ['ok' => false, 'amount_mismatch' => true]);
}

fulfil_order($order, (string)$payment['id']);
json_out(200, ['ok' => true]);
