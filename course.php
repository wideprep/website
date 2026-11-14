<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';

$c = course((string)($_GET['slug'] ?? ''));
if (!$c) {
    http_response_code(404);
    page_start('Page not found', 'This page could not be found.', ['noindex' => true]);
    echo '<section><div class="wrap prose"><h1>Course not found</h1><p><a href="/">Go to the home page</a></p></div></section>';
    page_end();
    exit;
}

page_start($c['title'] . ' — ' . $c['tagline'], $c['summary'], ['canonical' => '/courses/' . $c['slug']]);
?>
<section class="course-hero">
  <div class="wrap course-layout">
    <div>
      <span class="eyebrow"><?= e($c['format']) ?> course</span>
      <h1><?= e($c['title']) ?></h1>
      <p class="lead"><?= e($c['tagline']) ?>. <?= e($c['summary']) ?></p>
      <ul class="meta">
        <li><?= e($c['format']) ?> online</li>
        <li>Beginner friendly</li>
        <li><?= e(rupees($c['price'])) ?> one-time</li>
      </ul>

      <h2>What you’ll learn</h2>
      <ul class="ticks">
        <?php foreach ($c['learn'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
      </ul>

      <h2>Who it’s for</h2>
      <ul class="chips" style="margin-bottom:2rem">
        <?php foreach ($c['for'] as $who): ?><li><?= e($who) ?></li><?php endforeach; ?>
      </ul>

      <h2>Schedule &amp; access</h2>
      <p><?= e($c['schedule']) ?>. Your joining link appears on screen right after payment and is emailed to you.</p>
    </div>

    <aside id="enrol" class="course-card">
      <span class="tag live"><?= e($c['format']) ?></span>
      <h3>Enrol in <?= e($c['title']) ?></h3>
      <div class="price"><b><?= e(rupees($c['price'])) ?></b><span>one-time payment</span></div>
      <form id="enrol-form" novalidate>
        <input type="hidden" name="course" value="<?= e($c['slug']) ?>">
        <div class="field"><label for="name">Full name</label><input id="name" name="name" required minlength="2" maxlength="100" autocomplete="name"></div>
        <div class="field"><label for="email">Email (your access link is sent here)</label><input id="email" name="email" type="email" required autocomplete="email"></div>
        <div class="field"><label for="phone">Mobile number</label><input id="phone" name="phone" type="tel" required pattern="\+?[0-9 ]{10,16}" inputmode="tel" autocomplete="tel" placeholder="10-digit mobile"></div>
        <button class="btn btn-block" type="submit">Pay <?= e(rupees($c['price'])) ?> &amp; enrol</button>
        <p class="msg" role="status" aria-live="polite"></p>
        <p class="fine">Secure payment by Razorpay. By paying you agree to our <a href="/terms">Terms</a> and <a href="/refund-policy">no-refund policy</a>.</p>
      </form>
    </aside>
  </div>
</section>
<?php page_end(['https://checkout.razorpay.com/v1/checkout.js', '/assets/js/checkout.js?v=' . filemtime(__DIR__ . '/assets/js/checkout.js')]); ?>
