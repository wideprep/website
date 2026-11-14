<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
$b = BUSINESS;
page_start('Privacy Policy', 'How WidePrep collects, uses and protects your personal information.');
?>
<section class="page-head"><div class="wrap prose">
<span class="eyebrow">Policies</span>
<h1>Privacy Policy</h1>
<p class="fine">Last updated: 14 November 2026</p>

<p><b><?= e($b['name']) ?></b> (“WidePrep”, “we”) respects your privacy. This policy explains what personal information we collect on wideprep.in and how we use it.</p>

<h2>Information we collect</h2>
<ul>
  <li><b>When you enrol:</b> your name, email address and mobile number, the course you buy, and payment details we receive from Razorpay (payment ID, amount, status). We never receive your full card, UPI PIN or bank login details.</li>
  <li><b>When you contact us:</b> your name, email, mobile number and message.</li>
  <li><b>Technical data:</b> our hosting provider keeps standard server logs (such as IP address and browser type) for security and troubleshooting.</li>
</ul>

<h2>How we use it</h2>
<ul>
  <li>To process your payment and deliver your course access and joining links.</li>
  <li>To send you information about the course you bought, such as schedules or changes.</li>
  <li>To reply to your questions and provide support.</li>
  <li>To meet legal, tax and accounting obligations.</li>
</ul>
<p>We do not sell or rent your personal information.</p>

<h2>Who we share it with</h2>
<ul>
  <li><b>Razorpay</b>, to process payments, under their own privacy policy.</li>
  <li><b>Hostinger</b>, our website and email hosting provider, which stores our website data.</li>
  <li>Authorities, where the law requires it.</li>
</ul>

<h2>How long we keep it</h2>
<p>We keep order records for as long as needed for accounting and legal purposes, and enquiry messages for up to two years.</p>

<h2>Your choices</h2>
<p>You can ask us to access, correct or delete your personal information, or to stop sending you course updates, by emailing <a href="mailto:<?= e($b['email']) ?>"><?= e($b['email']) ?></a>. Some records may need to be kept to meet legal requirements.</p>

<h2>Cookies</h2>
<p>This website does not use advertising or tracking cookies. The payment window provided by Razorpay may use cookies needed to process payments securely.</p>

<h2>Contact</h2>
<p><?= e($b['name']) ?>, <?= e($b['address']) ?> · <a href="mailto:<?= e($b['email']) ?>"><?= e($b['email']) ?></a></p>
</div></section>
<?php page_end(); ?>
