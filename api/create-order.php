<?php
// POST /api/create-order  { amount: <paise>, name?, phone?, purpose? }
// -> { order_id, amount, currency, key_id }

declare(strict_types=1);
require __DIR__ . '/_razorpay.php';

rzp_require_post();
$in = rzp_input();

$amount = filter_var($in['amount'] ?? null, FILTER_VALIDATE_INT);
if ($amount === false || $amount === null) {
    rzp_json(400, ['error' => 'Amount is required and must be a whole number of paise.']);
}
if ($amount < RZP_MIN_AMOUNT) {
    rzp_json(400, ['error' => 'Minimum amount is ₹1 (100 paise).']);
}
if ($amount > RZP_MAX_AMOUNT) {
    rzp_json(400, ['error' => 'Amount is above the allowed limit.']);
}

$clean = fn($v, int $max) => mb_substr(trim(strip_tags((string)($v ?? ''))), 0, $max);
$notes = array_filter([
    'name'    => $clean($in['name'] ?? '', 100),
    'phone'   => $clean($in['phone'] ?? '', 20),
    'purpose' => $clean($in['purpose'] ?? '', 100),
], fn($v) => $v !== '');

$cfg = rzp_config();
$payload = [
    'amount'   => $amount,
    'currency' => RZP_CURRENCY,
    'receipt'  => 'wp_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)),
    'notes'    => (object)$notes,
];

$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_USERPWD        => $cfg['key_id'] . ':' . $cfg['key_secret'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20,
]);
$resp = curl_exec($ch);
$status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($resp === false) {
    error_log('Razorpay create order: network error: ' . $curlErr);
    rzp_json(500, ['error' => 'Could not reach the payment gateway. Please try again.']);
}

$order = json_decode($resp, true);
if ($status === 401) {
    error_log('Razorpay create order: authentication failed (check key id/secret)');
    rzp_json(401, ['error' => 'Payment gateway authentication failed.']);
}
if ($status < 200 || $status >= 300 || empty($order['id'])) {
    error_log('Razorpay create order failed: HTTP ' . $status . ' ' . $resp);
    rzp_json(500, ['error' => $order['error']['description'] ?? 'Could not create the payment order.']);
}

rzp_json(200, [
    'order_id' => $order['id'],
    'amount'   => $order['amount'],
    'currency' => $order['currency'],
    'key_id'   => $cfg['key_id'],   // public key — safe for the browser
]);
