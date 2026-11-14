<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';

$c = course('ai-toolkit');
page_start('WidePrep', 'WidePrep runs live, beginner-friendly online courses. Start with AI Toolkit: learn to let AI simplify your everyday tasks — for just ' . rupees($c['price']) . '.', ['canonical' => '/']);
?>
<section class="hero">
  <div class="wrap hero-grid">
    <div>
      <span class="eyebrow">Live online courses</span>
      <h1>Learn practical skills, <span class="highlight">live</span>, in plain language.</h1>
      <p class="lead">WidePrep teaches the skills that make everyday work easier. Our first course shows you how to use AI tools to save hours every week — no technical background needed.</p>
      <div class="cta">
        <a class="btn" href="/courses/<?= e($c['slug']) ?>#enrol">Enrol in <?= e($c['title']) ?> — <?= e(rupees($c['price'])) ?></a>
        <a class="btn btn-ghost" href="/courses/<?= e($c['slug']) ?>">See what you’ll learn</a>
      </div>
      <ul class="trust">
        <li>Live, interactive session</li>
        <li>Beginner friendly</li>
        <li>Secure payment by Razorpay</li>
      </ul>
    </div>

    <article class="course-card" aria-label="<?= e($c['title']) ?> course">
      <span class="tag live"><?= e($c['format']) ?></span>
      <h3><?= e($c['title']) ?></h3>
      <p class="tagline"><?= e($c['tagline']) ?></p>
      <ul class="ticks">
        <?php foreach (array_slice($c['learn'], 0, 4) as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
      </ul>
      <div class="price"><b><?= e(rupees($c['price'])) ?></b><span>one-time · instant access by email</span></div>
      <a class="btn btn-block" href="/courses/<?= e($c['slug']) ?>#enrol">Enrol now</a>
    </article>
  </div>
</section>

<section class="alt">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow">How it works</span>
      <h2>From sign-up to class in minutes</h2>
    </div>
    <div class="grid-3">
      <div class="card"><span class="num">1</span><h3>Choose your course</h3><p>Read what’s covered and who it’s for.</p></div>
      <div class="card"><span class="num">2</span><h3>Pay securely</h3><p>UPI, cards, net banking or wallets through Razorpay.</p></div>
      <div class="card"><span class="num">3</span><h3>Get your access link</h3><p>Shown right after payment and sent to your email.</p></div>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow">Who it’s for</span>
      <h2>Made for busy people, not programmers</h2>
      <p class="lead">If you use a phone or laptop for work or study, you can use AI to do it faster. We show you how, step by step.</p>
    </div>
    <ul class="chips">
      <?php foreach ($c['for'] as $who): ?><li><?= e($who) ?></li><?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="band">
  <div class="wrap band-inner">
    <div>
      <h2>Let AI <span class="highlight">simplify</span> your everyday tasks</h2>
      <p>Join the live <?= e($c['title']) ?> session for <?= e(rupees($c['price'])) ?>.</p>
    </div>
    <a class="btn" href="/courses/<?= e($c['slug']) ?>#enrol">Reserve my seat</a>
  </div>
</section>

<section id="faq">
  <div class="wrap">
    <div class="section-head"><span class="eyebrow">FAQ</span><h2>Common questions</h2></div>
    <div class="faq">
      <details><summary>Do I need any technical knowledge?</summary><p>No. The course is designed for complete beginners. If you can use WhatsApp and email, you can follow along.</p></details>
      <details><summary>How do I join the live session?</summary><p>Right after payment you’ll see your access page, and we email you the joining link and schedule at the address you enter.</p></details>
      <details><summary>Which payment methods are accepted?</summary><p>UPI, debit and credit cards, net banking and popular wallets, processed securely by Razorpay. We never see or store your card details.</p></details>
      <details><summary>I paid but didn’t get an email. What now?</summary><p>Check your spam/promotions folder first. If it isn’t there, write to <a href="mailto:<?= e(BUSINESS['email']) ?>"><?= e(BUSINESS['email']) ?></a> with your payment ID and we’ll resend it.</p></details>
      <details><summary>Can I get a refund?</summary><p>All sales are final; please read our <a href="/refund-policy">refund policy</a> before enrolling.</p></details>
    </div>
  </div>
</section>
<?php page_end(); ?>
