<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
$b = BUSINESS;
page_start('Delivery Policy', 'How WidePrep delivers course access after payment. All courses are delivered online; nothing is shipped.');
?>
<section class="page-head"><div class="wrap prose">
<span class="eyebrow">Policies</span>
<h1>Delivery Policy</h1>
<p class="fine">Last updated: 14 November 2026</p>

<h2>Online delivery only</h2>
<p>All WidePrep courses are delivered online. No physical goods are shipped.</p>

<h2>When you get access</h2>
<ul>
  <li>Immediately after a successful payment, your personal access page opens with your course details and joining link.</li>
  <li>At the same time, we email the access details to the email address you entered.</li>
  <li>For live sessions, the session date and time are included in the email. If the joining link is not ready yet, we email it to you before the session starts.</li>
</ul>

<h2>Didn’t receive it?</h2>
<p>Please check your spam or promotions folder. If you haven’t received your access details within 24 hours of payment, email <a href="mailto:<?= e($b['email']) ?>"><?= e($b['email']) ?></a> with your payment ID and we will resend them.</p>

<h2>Contact</h2>
<p><?= e($b['name']) ?>, <?= e($b['address']) ?> · <a href="mailto:<?= e($b['email']) ?>"><?= e($b['email']) ?></a></p>
</div></section>
<?php page_end(); ?>
