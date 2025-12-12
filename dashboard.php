<?php
declare(strict_types=1);
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";
require_login();

$page_title = "Dashboard";
$uid = current_user_id();

$userStmt = $pdo->prepare("SELECT name, default_rate, week_start FROM users WHERE id=?");
$userStmt->execute([$uid]);
$user = $userStmt->fetch();

$weekStart = $user['week_start'] ?? 'mon';

$selected = $_GET['date'] ?? (new DateTimeImmutable("now"))->format("Y-m-d");
try { $selectedDate = new DateTimeImmutable($selected); }
catch(Throwable $e){ $selectedDate = new DateTimeImmutable("now"); }

$start = week_start_date($selectedDate, $weekStart);
$end = $start->modify("+7 days");

$stmt = $pdo->prepare("
  SELECT s.*, j.job_name
  FROM shifts s
  LEFT JOIN jobs j ON j.id = s.job_id
  WHERE s.user_id = ?
    AND s.shift_date >= ?
    AND s.shift_date < ?
  ORDER BY s.shift_date ASC, s.start_time ASC
");
$stmt->execute([$uid, $start->format("Y-m-d"), $end->format("Y-m-d")]);
$shifts = $stmt->fetchAll();

$totalMinutes = 0;
$totalPay = 0.0;
foreach ($shifts as $s) {
  $totalMinutes += (int)$s['computed_minutes'];
  $totalPay += (float)$s['computed_pay'];
}

$prev = $start->modify("-7 days")->format("Y-m-d");
$next = $start->modify("+7 days")->format("Y-m-d");

require __DIR__ . "/includes/header.php";
?>
<div class="grid grid--2">
  <section class="card">
    <h1>Hello, <?= h($user['name'] ?? "User") ?> 👋</h1>
    <p class="muted">Week: <span class="pill"><?= h($start->format("D, d M Y")) ?> → <?= h($end->modify("-1 day")->format("D, d M Y")) ?></span></p>

    <div class="btn-row" style="margin-top:10px;">
      <a class="btn btn--ghost" href="dashboard.php?date=<?= h($prev) ?>">← Previous week</a>
      <a class="btn btn--ghost" href="dashboard.php?date=<?= h($next) ?>">Next week →</a>
      <a class="btn" href="add_shift.php">+ Add shift</a>
    </div>

    <div class="hr"></div>

    <div class="grid grid--2">
      <div class="card" style="padding:14px;">
        <div class="muted small">Total hours</div>
        <div style="font-size:28px;font-weight:850;"><?= h(minutes_to_hhmm($totalMinutes)) ?></div>
      </div>
      <div class="card" style="padding:14px;">
        <div class="muted small">Total pay</div>
        <div style="font-size:28px;font-weight:850;">£<?= h(number_format($totalPay, 2)) ?></div>
      </div>
    </div>

    <p class="muted small" style="margin-top:12px;">
      Tip: set your default hourly rate in <a href="settings.php" style="color:var(--text)">Settings</a>.
    </p>
  </section>

  <section class="card">
    <h2>This week’s shifts</h2>
    <p class="muted">All your shifts for the selected week.</p>

    <div class="hr"></div>

    <?php if (!$shifts): ?>
      <p class="muted">No shifts yet. Add your first shift!</p>
    <?php else: ?>
      <div style="overflow:auto;">
        <table class="table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Time</th>
              <th>Break</th>
              <th>Hours</th>
              <th class="right">Rate</th>
              <th class="right">Pay</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($shifts as $s): ?>
              <tr>
                <td>
                  <div><strong><?= h((new DateTimeImmutable($s['shift_date']))->format("D, d M")) ?></strong></div>
                  <div class="muted small"><?= h($s['job_name'] ?? "—") ?></div>
                </td>
                <td><?= h(substr($s['start_time'],0,5)) ?> → <?= h(substr($s['end_time'],0,5)) ?></td>
                <td><?= h((string)$s['break_minutes']) ?> min</td>
                <td><?= h(minutes_to_hhmm((int)$s['computed_minutes'])) ?></td>
                <td class="right">£<?= h(number_format((float)$s['hourly_rate'], 2)) ?></td>
                <td class="right">£<?= h(number_format((float)$s['computed_pay'], 2)) ?></td>
                <td>
                  <div class="btn-row">
                    <a class="btn btn--ghost" href="edit_shift.php?id=<?= (int)$s['id'] ?>">Edit</a>
                    <a class="btn btn--danger" href="delete_shift.php?id=<?= (int)$s['id'] ?>" onclick="return confirm('Delete this shift?')">Delete</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</div>

<?php require __DIR__ . "/includes/footer.php"; ?>
