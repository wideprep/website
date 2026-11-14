<?php
// POST /api/enquiry  { name, email, phone, message, website (honeypot) }

declare(strict_types=1);
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/mailer.php';

require_post();
$in = request_data();

if (!empty($in['website'])) json_out(200, ['ok' => true]); // spam bot filled the hidden field

[$who, $err] = validate_contact($in);
if ($err) json_out(400, ['error' => $err]);
$message = mb_substr(trim(strip_tags((string)($in['message'] ?? ''))), 0, 2000);

try {
    db()->prepare('INSERT INTO enquiries (name, email, phone, message, created_at) VALUES (?, ?, ?, ?, ?)')
        ->execute([$who['name'], $who['email'], $who['phone'], $message, now()]);
} catch (\Throwable $ex) {
    error_log('Saving enquiry failed: ' . $ex->getMessage());
    json_out(500, ['error' => 'Sorry, something went wrong. Please email us at ' . BUSINESS['email'] . '.']);
}

$text = "Name: {$who['name']}\nEmail: {$who['email']}\nPhone: {$who['phone']}\n\n$message";
send_mail(env('MAIL_NOTIFY', BUSINESS['email']), 'WidePrep', 'New enquiry from ' . $who['name'],
    mail_layout('New website enquiry', nl2br(e($text))), $text, $who['email']);

json_out(200, ['ok' => true]);
