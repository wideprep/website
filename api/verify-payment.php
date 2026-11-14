<?php
// POST /api/verify-payment  { razorpay_order_id, razorpay_payment_id, razorpay_signature }
// -> 200 { verified: true, payment_id } only when the signature matches.

declare(strict_types=1);
require __DIR__ . '/_razorpay.php';

rzp_require_post();
$in = rzp_input();

$orderId   = trim((string)($in['razorpay_order_id'] ?? ''));
$paymentId = trim((string)($in['razorpay_payment_id'] ?? ''));
$signature = trim((string)($in['razorpay_signature'] ?? ''));

if ($orderId === '' || $paymentId === '' || $signature === '') {
    rzp_json(400, ['verified' => false, 'error' => 'Missing payment details.']);
}

$cfg = rzp_config();
$expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $cfg['key_secret']);

if (!hash_equals($expected, $signature)) {
    error_log("Razorpay signature mismatch for order $orderId payment $paymentId");
    rzp_json(400, ['verified' => false, 'error' => 'Payment could not be verified.']);
}

rzp_json(200, ['verified' => true, 'order_id' => $orderId, 'payment_id' => $paymentId]);
