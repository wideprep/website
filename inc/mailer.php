<?php
// Sends email from contact@wideprep.in.
// Uses Hostinger SMTP when SMTP_PASS is set in the server settings file
// (best delivery); otherwise falls back to the server's built-in mail().

declare(strict_types=1);

require_once __DIR__ . '/lib/PHPMailer/Exception.php';
require_once __DIR__ . '/lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

function send_mail(string $toEmail, string $toName, string $subject, string $html, string $text, ?string $replyTo = null): bool {
    if (env('MAIL_DISABLED') === '1') { error_log("Mail disabled; would send '$subject' to $toEmail"); return true; }

    $m = new PHPMailer(true);
    try {
        $m->CharSet = 'UTF-8';
        if (env('SMTP_PASS') !== '') {
            $m->isSMTP();
            $m->Host       = env('SMTP_HOST', 'smtp.hostinger.com');
            $m->Port       = (int)env('SMTP_PORT', '465');
            $m->SMTPAuth   = true;
            $m->Username   = env('SMTP_USER', BUSINESS['email']);
            $m->Password   = env('SMTP_PASS');
            $m->SMTPSecure = $m->Port === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $m->Timeout    = 15;
        } else {
            $m->isMail();
        }
        $m->setFrom(env('MAIL_FROM', BUSINESS['email']), BUSINESS['brand']);
        $m->addAddress($toEmail, $toName);
        if ($replyTo) $m->addReplyTo($replyTo);
        $m->Subject = $subject;
        $m->isHTML(true);
        $m->Body    = $html;
        $m->AltBody = $text;
        return $m->send();
    } catch (\Throwable $ex) {
        error_log('Mail to ' . $toEmail . ' failed: ' . $m->ErrorInfo . ' ' . $ex->getMessage());
        return false;
    }
}

/** Simple branded HTML wrapper for emails. */
function mail_layout(string $heading, string $bodyHtml): string {
    $logo = SITE_URL . '/assets/brand/wideprep-email-logo.png';
    return '<!doctype html><html><body style="margin:0;background:#f3f7f9;font-family:Arial,Helvetica,sans-serif;color:#0B4F6C">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:24px 12px">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fff;border-radius:12px;padding:28px">'
        . '<tr><td><img src="' . e($logo) . '" alt="WidePrep" width="160" style="display:block;margin-bottom:20px">'
        . '<h1 style="font-size:22px;margin:0 0 14px">' . e($heading) . '</h1>'
        . '<div style="font-size:15px;line-height:1.6;color:#23424f">' . $bodyHtml . '</div>'
        . '<p style="font-size:12px;color:#6b8592;margin-top:28px;border-top:1px solid #e3ecef;padding-top:14px">'
        . e(BUSINESS['name']) . ' · ' . e(BUSINESS['address']) . ' · ' . e(BUSINESS['email']) . '</p>'
        . '</td></tr></table></td></tr></table></body></html>';
}
