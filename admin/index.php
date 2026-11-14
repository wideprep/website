<?php
// Private admin page: sales and enquiries. Password hash lives in the server settings file (ADMIN_PASSWORD_HASH).
declare(strict_types=1);
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/orders.php';
require __DIR__ . '/../inc/layout.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict', 'secure' => !empty($_SERVER['HTTPS']), 'path' => '/admin']);
session_name('wpadmin');
session_start();
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');

$hash = env('ADMIN_PASSWORD_HASH');
$error = null;
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function check_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) { http_response_code(400); exit('Invalid request'); }
}

$action = $_POST['action'] ?? '';
if ($action === 'login') {
    check_csrf();
    if ($hash !== '' && password_verify((string)($_POST['password'] ?? ''), $hash)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: /admin/'); exit;
    }
    sleep(2);
    $error = $hash === '' ? 'Admin password is not set up on the server yet.' : 'Wrong password.';
} elseif ($action === 'logout') {
    check_csrf();
    $_SESSION = [];
    session_destroy();
    header('Location: /admin/'); exit;
} elseif ($action === 'resend' && !empty($_SESSION['admin'])) {
    check_csrf();
    $st = db()->prepare("SELECT * FROM orders WHERE id = ? AND status = 'paid'");
    $st->execute([(int)($_POST['id'] ?? 0)]);
    if ($o = $st->fetch()) {
        $ok = send_access_email($o);
        if ($ok) db()->prepare('UPDATE orders SET email_sent_at = ? WHERE id = ?')->execute([now(), $o['id']]);
        $_SESSION['flash'] = $ok ? 'Access email sent to ' . $o['email'] . '.' : 'Sending failed — check the email settings.';
    }
    header('Location: /admin/'); exit;
}

page_start('Admin', 'WidePrep admin', ['noindex' => true, 'canonical' => '/admin/']);

if (empty($_SESSION['admin'])): ?>
<section class="page-head"><div class="wrap" style="max-width:420px">
  <h1>Admin login</h1>
  <form method="post" class="card">
    <input type="hidden" name="action" value="login"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
    <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password" autofocus></div>
    <button class="btn btn-block">Log in</button>
    <?php if ($error): ?><p class="msg error"><?= e($error) ?></p><?php endif; ?>
  </form>
</div></section>
<?php page_end(); exit; endif;

$pdo = db();
$orders = $pdo->query('SELECT * FROM orders ORDER BY id DESC LIMIT 300')->fetchAll();
$enquiries = $pdo->query('SELECT * FROM enquiries ORDER BY id DESC LIMIT 200')->fetchAll();
$paid = array_filter($orders, fn($o) => $o['status'] === 'paid');
$revenue = array_sum(array_map(fn($o) => (int)$o['amount'], $paid));
$mode = str_starts_with(env('RAZORPAY_KEY_ID'), 'rzp_live_') ? 'LIVE' : 'TEST';
?>
<section class="page-head admin"><div class="wrap">
  <div class="band-inner" style="margin-bottom:1rem">
    <h1 style="margin:0">Dashboard</h1>
    <form method="post"><input type="hidden" name="action" value="logout"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><button class="btn btn-ghost btn-small">Log out</button></form>
  </div>
  <?php if ($flash): ?><p class="msg ok"><?= e($flash) ?></p><?php endif; ?>
  <div class="stats">
    <div class="card"><b><?= count($paid) ?></b>paid enrolments</div>
    <div class="card"><b><?= e(rupees($revenue)) ?></b>revenue (last 300 orders)</div>
    <div class="card"><b><?= count($enquiries) ?></b>enquiries</div>
    <div class="card"><b><?= $mode ?></b>Razorpay mode</div>
  </div>

  <h2>Orders</h2>
  <p class="fine">“Started” means the buyer opened the payment window but did not finish paying.</p>
  <div class="table-scroll"><table>
    <thead><tr><th>Date</th><th>Status</th><th>Course</th><th>Amount</th><th>Name</th><th>Email / Phone</th><th>Payment ID</th><th>Access email</th></tr></thead>
    <tbody>
    <?php foreach ($orders as $o): $c = course($o['course_slug']); ?>
      <tr>
        <td><?= e(date('d M Y, H:i', strtotime($o['paid_at'] ?: $o['created_at']))) ?></td>
        <td><span class="pill <?= e($o['status']) ?>"><?= $o['status'] === 'paid' ? 'Paid' : 'Started' ?></span></td>
        <td><?= e($c['title'] ?? $o['course_slug']) ?></td>
        <td><?= e(rupees((int)$o['amount'])) ?></td>
        <td><?= e($o['name']) ?></td>
        <td><?= e($o['email']) ?><br><?= e($o['phone']) ?></td>
        <td><?= e($o['rzp_payment_id'] ?? '—') ?></td>
        <td>
          <?php if ($o['status'] === 'paid'): ?>
            <?= $o['email_sent_at'] ? 'Sent ' . e(date('d M, H:i', strtotime($o['email_sent_at']))) : '<b>Not sent</b>' ?>
            <form method="post" style="margin-top:.3rem"><input type="hidden" name="action" value="resend"><input type="hidden" name="id" value="<?= (int)$o['id'] ?>"><input type="hidden" name="csrf" value="<?= e(csrf()) ?>"><button class="btn btn-ghost btn-small">Resend</button></form>
          <?php else: ?>—<?php endif; ?>
        </td>
      </tr>
    <?php endforeach; if (!$orders): ?><tr><td colspan="8">No orders yet.</td></tr><?php endif; ?>
    </tbody>
  </table></div>

  <h2 style="margin-top:2.5rem">Enquiries</h2>
  <div class="table-scroll"><table>
    <thead><tr><th>Date</th><th>Name</th><th>Email / Phone</th><th>Message</th></tr></thead>
    <tbody>
    <?php foreach ($enquiries as $q): ?>
      <tr><td><?= e(date('d M Y, H:i', strtotime($q['created_at']))) ?></td><td><?= e($q['name']) ?></td>
          <td><a href="mailto:<?= e($q['email']) ?>"><?= e($q['email']) ?></a><br><?= e($q['phone']) ?></td><td><?= nl2br(e($q['message'])) ?></td></tr>
    <?php endforeach; if (!$enquiries): ?><tr><td colspan="4">No enquiries yet.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div></section>
<?php page_end(); ?>
