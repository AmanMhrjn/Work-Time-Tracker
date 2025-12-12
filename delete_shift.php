<?php
declare(strict_types=1);
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/auth.php";
require_login();

$uid = current_user_id();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("DELETE FROM shifts WHERE id=? AND user_id=?");
$stmt->execute([$id, $uid]);

header("Location: dashboard.php");
exit;
