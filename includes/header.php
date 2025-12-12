<?php
declare(strict_types=1);
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/functions.php";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= isset($page_title) ? h($page_title) : "Work Tracker" ?></title>
  <link rel="stylesheet" href="assets/style.css" />
</head>
<body>
<header class="topbar">
  <div class="container topbar__inner">
    <a class="brand" href="dashboard.php">WorkTracker</a>
    <nav class="nav">
      <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="dashboard.php">Dashboard</a>
        <a href="add_shift.php">Add shift</a>
        <a href="settings.php">Settings</a>
        <a class="btn btn--ghost" href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
        <a class="btn" href="register.php">Register</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="container main">
