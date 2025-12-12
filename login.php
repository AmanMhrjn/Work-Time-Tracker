<?php
declare(strict_types=1);
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";

$page_title = "Login";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? "");
  $pass = $_POST['password'] ?? "";

  $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if (!$user || !password_verify($pass, $user['password_hash'])) {
    $error = "Invalid email or password.";
  } else {
    $_SESSION['user_id'] = (int)$user['id'];
    header("Location: dashboard.php");
    exit;
  }
}

require __DIR__ . "/includes/header.php";
?>
<div class="card" style="max-width:560px;margin:0 auto;">
  <h1>Login</h1>
  <p class="muted">Welcome back.</p>

  <?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>

  <form method="post">
    <label class="label">Email</label>
    <input class="input" type="email" name="email" value="<?= h($_POST['email'] ?? "") ?>" required>

    <label class="label">Password</label>
    <input class="input" type="password" name="password" required>

    <div class="btn-row" style="margin-top:14px;">
      <button class="btn" type="submit">Login</button>
      <a class="btn btn--ghost" href="register.php">Create account</a>
    </div>
  </form>
</div>
<?php require __DIR__ . "/includes/footer.php"; ?>
