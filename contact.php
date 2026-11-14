<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';

page_start('Contact us', 'Contact WidePrep — email ' . BUSINESS['email'] . ' or send us a message.');
?>
<section class="page-head"><div class="wrap"><span class="eyebrow">Contact</span><h1>Get in touch</h1>
  <p class="lead">Questions about a course or a payment? Send us a message and we’ll reply within one working day.</p></div></section>
<section style="padding-top:24px">
  <div class="wrap course-layout">
    <form id="enquiry-form" class="card" novalidate>
      <div class="field"><label for="name">Full name</label><input id="name" name="name" required autocomplete="name"></div>
      <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required autocomplete="email"></div>
      <div class="field"><label for="phone">Mobile number</label><input id="phone" name="phone" type="tel" required inputmode="tel" autocomplete="tel"></div>
      <div class="field"><label for="message">Message</label><textarea id="message" name="message" maxlength="2000"></textarea></div>
      <div class="hp" aria-hidden="true"><label for="website">Leave empty</label><input id="website" name="website" tabindex="-1" autocomplete="off"></div>
      <button class="btn" type="submit">Send message</button>
      <p class="msg" role="status" aria-live="polite"></p>
    </form>
    <div class="card">
      <h3>Email</h3>
      <p><a href="mailto:<?= e(BUSINESS['email']) ?>"><?= e(BUSINESS['email']) ?></a></p>
      <h3 style="margin-top:1.2rem">Address</h3>
      <p><?= e(BUSINESS['name']) ?><br><?= e(BUSINESS['address']) ?></p>
    </div>
  </div>
</section>
<?php page_end(); ?>
