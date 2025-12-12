<?php
declare(strict_types=1);
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";

$page_title = "Register";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? "");
  $email = trim($_POST['email'] ?? "");
  $pass = $_POST['password'] ?? "";

  if ($name === "" || $email === "" || $pass === "") {
    $error = "Please fill all fields.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email address.";
  } elseif (strlen($pass) < 6) {
    $error = "Password must be at least 6 characters.";
  } else {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
      $error = "Email already registered.";
    } else {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("INSERT INTO users (name,email,password_hash,default_rate,week_start) VALUES (?,?,?,?,?)");
      $stmt->execute([$name, $email, $hash, 0.00, 'mon']);
      header("Location: login.php");
      exit;
    }
  }
}

require __DIR__ . "/includes/header.php";
?>
<div class="card" style="max-width:560px;margin:0 auto;">
  <h1>Create account</h1>
  <p class="muted">Track shifts, hours, and weekly pay.</p>

  <?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>

  <form method="post">
    <label class="label">Name</label>
    <input class="input" name="name" value="<?= h($_POST['name'] ?? "") ?>" required>

    <label class="label">Email</label>
    <input class="input" type="email" name="email" value="<?= h($_POST['email'] ?? "") ?>" required>

    <label class="label">Password</label>
    <input class="input" type="password" name="password" required>

    <div class="btn-row" style="margin-top:14px;">
      <button class="btn" type="submit">Register</button>
      <a class="btn btn--ghost" href="login.php">I already have an account</a>
    </div>
  </form>
</div>
<?php require __DIR__ . "/includes/footer.php"; ?>
