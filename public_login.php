<?php
require __DIR__.'/../app_config.php';
require __DIR__.'/../app_helpers.php';
require __DIR__.'/../appCSRF.php';
require __DIR__.'/../app_auth.php';

if (current_user()) redirect('/'.($_SESSION['user']['role']==='admin'?'admin/dashboard.php':'student/dashboard.php'));

$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!csrf_verify($_POST['csrf'] ?? '')) $err='Invalid CSRF token.';
  else {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $stmt=$pdo->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
    $stmt->execute([$email]);
    $u=$stmt->fetch();
    if (!$u || !password_verify($pass, $u['password'])) $err='Invalid credentials.';
    else { login_user($u); redirect('/'.($u['role']==='admin'?'admin/dashboard.php':'student/dashboard.php')); }
  }
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="container py-5" style="max-width:480px;">
  <h3 class="mb-3">Online Exam | Login</h3>
  <?php if($err): ?><div class="alert alert-danger"><?=sanitize($err)?></div><?php endif; ?>
  <form method="post" class="vstack gap-3">
    <?= csrf_field() ?>
    <input class="form-control" name="email" type="email" placeholder="Email" required>
    <input class="form-control" name="password" type="password" placeholder="Password" required>
    <button class="btn btn-primary">Login</button>
  </form>
</div>
