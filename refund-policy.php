<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
$b = BUSINESS;
page_start('Refund & Cancellation Policy', 'WidePrep refund and cancellation policy: all course payments are final.');
?>
<section class="page-head"><div class="wrap prose">
<span class="eyebrow">Policies</span>
<h1>Refund &amp; Cancellation Policy</h1>
<p class="fine">Last updated: 14 November 2026</p>

<h2>No refunds</h2>
<p>All payments made to <b><?= e($b['name']) ?></b> for courses are <b>final and non-refundable</b>. Once your payment is successful, it cannot be cancelled or refunded, including if you are unable to attend a live session. Please read the course details carefully before you pay.</p>

<h2>Cancellation</h2>
<p>Because access is delivered immediately after payment, enrolments cannot be cancelled once the payment is complete.</p>

<h2>Duplicate or failed payments</h2>
<p>If you were charged more than once for the same enrolment, or money was deducted for a payment that failed, please email <a href="mailto:<?= e($b['email']) ?>"><?= e($b['email']) ?></a> with your payment ID(s). Amounts deducted for failed payments are normally reversed to your account automatically by your bank or Razorpay within 5–7 working days.</p>

<h2>Contact</h2>
<p><?= e($b['name']) ?>, <?= e($b['address']) ?> · <a href="mailto:<?= e($b['email']) ?>"><?= e($b['email']) ?></a></p>
</div></section>
<?php page_end(); ?>
