<?php
// Shared page header and footer.

declare(strict_types=1);

function page_start(string $title, string $description, array $opt = []): void {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $canonical = SITE_URL . ($opt['canonical'] ?? $path);
    $full = $title === BUSINESS['brand'] ? BUSINESS['brand'] . ' — Live AI courses' : $title . ' | ' . BUSINESS['brand'];
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    if (!empty($opt['noindex'])) header('X-Robots-Tag: noindex');
    $nav = ['/' => 'Home', '/courses/ai-toolkit' => 'AI Toolkit', '/#faq' => 'FAQ', '/contact' => 'Contact'];
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($full) ?></title>
<meta name="description" content="<?= e($description) ?>">
<?php if (!empty($opt['noindex'])): ?><meta name="robots" content="noindex"><?php endif; ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<meta name="theme-color" content="#0B4F6C">
<meta property="og:site_name" content="WidePrep">
<meta property="og:title" content="<?= e($full) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= SITE_URL ?>/og-link-preview-1200x630.png">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon-180x180.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/site.css?v=<?= filemtime(SITE_ROOT . '/assets/css/site.css') ?>">
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<header class="site-header">
  <nav class="wrap nav" aria-label="Main">
    <a class="logo" href="/"><img src="/assets/brand/wideprep-logo-color.svg" alt="WidePrep" width="132" height="68"></a>
    <button class="menu-btn" aria-label="Menu" aria-expanded="false" aria-controls="menu"><span></span><span></span><span></span></button>
    <ul id="menu">
      <?php foreach ($nav as $href => $label): ?>
        <li><a href="<?= e($href) ?>"<?= $href === $path ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
      <?php endforeach; ?>
      <li><a class="btn btn-small" href="/courses/ai-toolkit#enrol">Enrol now</a></li>
    </ul>
  </nav>
</header>
<main id="main">
<?php
}

function page_end(array $scripts = []): void {
    $year = date('Y');
    ?>
</main>
<footer class="site-footer">
  <div class="wrap footer-grid">
    <div>
      <img src="/assets/brand/wideprep-logo-dark.svg" alt="WidePrep" width="140" height="72" class="footer-logo">
      <p>Live, practical courses that help you get more done.</p>
    </div>
    <div>
      <h3>Courses</h3>
      <ul>
        <?php foreach (COURSES as $slug => $c): ?>
          <li><a href="/courses/<?= e($slug) ?>"><?= e($c['title']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div>
      <h3>Policies</h3>
      <ul>
        <li><a href="/terms">Terms &amp; Conditions</a></li>
        <li><a href="/privacy">Privacy Policy</a></li>
        <li><a href="/refund-policy">Refund &amp; Cancellation</a></li>
        <li><a href="/delivery-policy">Delivery Policy</a></li>
      </ul>
    </div>
    <div>
      <h3>Contact</h3>
      <address>
        <?= e(BUSINESS['name']) ?><br>
        <?= e(BUSINESS['address']) ?><br>
        <a href="mailto:<?= e(BUSINESS['email']) ?>"><?= e(BUSINESS['email']) ?></a>
      </address>
    </div>
  </div>
  <div class="wrap footer-bottom">© <?= $year ?> <?= e(BUSINESS['name']) ?>. All rights reserved.</div>
</footer>
<script src="/assets/js/site.js?v=<?= filemtime(SITE_ROOT . '/assets/js/site.js') ?>" defer></script>
<?php foreach ($scripts as $s): ?><script src="<?= e($s) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
<?php
}
