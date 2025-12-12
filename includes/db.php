<?php
declare(strict_types=1);

$DB_HOST = "localhost";
$DB_NAME = "worktracker";
$DB_USER = "root";
$DB_PASS = "aman"; // put your MySQL password here

try {
  $pdo = new PDO(
    "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
    $DB_USER,
    $DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
  );
} catch (Throwable $e) {
  http_response_code(500);
  echo "Database connection failed.";
  exit;
}
