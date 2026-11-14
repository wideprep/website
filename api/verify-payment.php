<?php
// POST /api/verify-payment  { razorpay_order_id, razorpay_payment_id, razorpay_signature }
// Marks the order paid ONLY when HMAC-SHA256(order_id|payment_id, key_secret) matches.
// -> 200 { verified: true, redirect } | 400 { verified: false, error }

declare(strict_types=1);
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/orders.php';

require_post();
$in = request_data();

$orderId   = trim((string)($in['razorpay_order_id'] ?? ''));
$paymentId = trim((string)($in['razorpay_payment_id'] ?? ''));
$signature = trim((string)($in['razorpay_signature'] ?? ''));

if ($orderId === '' || $paymentId === '' || $signature === '') {
    json_out(400, ['verified' => false, 'error' => 'Missing payment details.']);
}
if (!razorpay_configured()) json_out(500, ['verified' => false, 'error' => 'Payments are not configured.']);

$expected = hash_hmac('sha256', $orderId . '|' . $paymentId, env('RAZORPAY_KEY_SECRET'));
if (!hash_equals($expected, $signature)) {
    error_log("Razorpay signature mismatch: order $orderId payment $paymentId");
    json_out(400, ['verified' => false, 'error' => 'We could not verify this payment. If money was deducted, please contact us with your payment ID.']);
}

$order = find_order_by_rzp_id($orderId);
if (!$order) {
    error_log("Verified payment for unknown order $orderId");
    json_out(400, ['verified' => false, 'error' => 'Order not found. Please contact us with your payment ID.']);
}

$order = fulfil_order($order, $paymentId);
json_out(200, ['verified' => true, 'payment_id' => $paymentId, 'redirect' => '/thank-you?t=' . urlencode($order['access_token'])]);
