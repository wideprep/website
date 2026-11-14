<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
$b = BUSINESS;
page_start('Terms & Conditions', 'Terms and conditions for using wideprep.in and enrolling in WidePrep courses.');
?>
<section class="page-head"><div class="wrap prose">
<span class="eyebrow">Policies</span>
<h1>Terms &amp; Conditions</h1>
<p class="fine">Last updated: 14 November 2026</p>

<p>These terms apply to your use of <b>wideprep.in</b> and to any course you buy from us. The website is operated by <b><?= e($b['name']) ?></b>, <?= e($b['address']) ?> (“WidePrep”, “we”, “us”). By using this website or paying for a course, you agree to these terms.</p>

<h2>1. Courses</h2>
<p>Each course page describes the course, its format (for example, a live online session), its price and its schedule. Course content is for your personal learning only. We may make reasonable changes to course content or instructors to improve the course.</p>

<h2>2. Enrolment and payment</h2>
<ul>
  <li>Prices are shown in Indian Rupees (INR) on each course page.</li>
  <li>Payments are processed securely by our payment partner, Razorpay. We do not see or store your card, UPI or bank details.</li>
  <li>Your enrolment is confirmed once the payment succeeds. You will see your access page immediately and receive the details by email.</li>
  <li>You must give a correct name, email address and mobile number, because we use them to deliver your course access.</li>
</ul>

<h2>3. Access and sharing</h2>
<p>Your joining link or course access is for you alone. You must not share, resell, record, copy or redistribute course links, sessions or materials. We may remove access without refund if we find that access has been shared or misused.</p>

<h2>4. Live sessions</h2>
<p>Live sessions take place at the date and time we communicate to you. If we have to reschedule a session, we will inform you by email and give you access to the new date. You are responsible for having a working internet connection and device to attend.</p>

<h2>5. Refunds</h2>
<p>All payments are final. Please read our <a href="/refund-policy">Refund &amp; Cancellation Policy</a> before enrolling.</p>

<h2>6. Intellectual property</h2>
<p>All website and course content, including text, slides, videos, the WidePrep name and logo, belongs to WidePrep or its licensors and may not be used without our written permission.</p>

<h2>7. Limitation of liability</h2>
<p>Our courses are educational. We do our best to keep information accurate, but we do not guarantee any particular result from using what you learn. To the extent permitted by law, our total liability to you for any claim is limited to the amount you paid for the course concerned.</p>

<h2>8. Governing law</h2>
<p>These terms are governed by the laws of India. Courts in <?= e($b['city']) ?>, Telangana have exclusive jurisdiction over any dispute.</p>

<h2>9. Contact</h2>
<p>Questions about these terms: <a href="mailto:<?= e($b['email']) ?>"><?= e($b['email']) ?></a>, <?= e($b['address']) ?>.</p>
</div></section>
<?php page_end(); ?>
