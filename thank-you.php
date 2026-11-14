<?php
// Shows the buyer's access link. Reached only through a private token after a verified payment.
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';

$token = (string)($_GET['t'] ?? '');
$order = null;
if (preg_match('/^[a-f0-9]{32}$/', $token)) {
    $st = db()->prepare("SELECT * FROM orders WHERE access_token = ? AND status = 'paid'");
    $st->execute([$token]);
    $order = $st->fetch() ?: null;
}
header('Cache-Control: no-store');
page_start('Your course access', 'Your WidePrep course access.', ['noindex' => true, 'canonical' => '/thank-you']);
?>
<section class="page-head"><div class="wrap prose">
<?php if (!$order): ?>
  <h1>Link not found</h1>
  <div class="notice"><p>This access link is invalid or has expired. If you have paid, please check the email we sent you, or write to
  <a href="mailto:<?= e(BUSINESS['email']) ?>"><?= e(BUSINESS['email']) ?></a> with your payment ID.</p></div>
<?php else:
  $c = course($order['course_slug']);
  $link = course_access_link($order['course_slug']); ?>
  <span class="eyebrow">Payment successful</span>
  <h1>You’re in, <?= e(explode(' ', $order['name'])[0]) ?>! 🎉</h1>
  <div class="notice ok">
    <p>Your seat in <b><?= e($c['title'] ?? $order['course_slug']) ?></b> is confirmed. We’ve also emailed these details to <b><?= e($order['email']) ?></b>.</p>
    <?php if ($link !== ''): ?>
      <p><a class="btn" href="<?= e($link) ?>" target="_blank" rel="noopener">Open my course / joining link</a></p>
    <?php else: ?>
      <p>Your joining link will be emailed to you before the session starts.</p>
    <?php endif; ?>
    <p><b>Schedule:</b> <?= e($c['schedule'] ?? '') ?></p>
  </div>
  <p class="fine">Amount paid: <?= e(rupees((int)$order['amount'])) ?> · Payment ID: <?= e($order['rzp_payment_id']) ?> · Order: <?= e($order['receipt']) ?><br>
  Bookmark this page — it’s your personal access page. Please don’t share it.</p>
<?php endif; ?>
</div></section>
<section style="padding-top:0"><div class="wrap"></div></section>
<?php page_end(); ?>
