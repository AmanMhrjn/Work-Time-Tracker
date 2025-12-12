<?php

declare(strict_types=1);
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";
require_login();

$page_title = "Add shift";
$uid = current_user_id();
$error = "";

$userStmt = $pdo->prepare("SELECT default_rate FROM users WHERE id=?");
$userStmt->execute([$uid]);
$u = $userStmt->fetch();
$defaultRate = (float)($u['default_rate'] ?? 0);

$jobsStmt = $pdo->prepare("SELECT id, job_name, default_rate FROM jobs WHERE user_id=? ORDER BY job_name ASC");
$jobsStmt->execute([$uid]);
$jobs = $jobsStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $shift_date = $_POST['shift_date'] ?? "";
  $start_time = $_POST['start_time'] ?? "";
  $end_time = $_POST['end_time'] ?? "";
  $break_minutes = (int)($_POST['break_minutes'] ?? 0);
  $hourly_rate = (float)($_POST['hourly_rate'] ?? 0);
  $job_id = ($_POST['job_id'] ?? "") !== "" ? (int)$_POST['job_id'] : null;
  $notes = trim($_POST['notes'] ?? "");

  if ($shift_date === "" || $start_time === "" || $end_time === "") {
    $error = "Please fill date, start time and end time.";
  } elseif ($break_minutes < 0) {
    $error = "Break minutes cannot be negative.";
  } else {
    [$mins, $payStr] = calculate_minutes_and_pay($start_time, $end_time, $break_minutes, $hourly_rate);

    $stmt = $pdo->prepare("
      INSERT INTO shifts (user_id, job_id, shift_date, start_time, end_time, break_minutes, hourly_rate, notes, computed_minutes, computed_pay)
      VALUES (?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->execute([
      $uid,
      $job_id,
      $shift_date,
      $start_time,
      $end_time,
      $break_minutes,
      $hourly_rate,
      $notes !== "" ? $notes : null,
      $mins,
      $payStr
    ]);

    header("Location: dashboard.php");
    exit;
  }
}

require __DIR__ . "/includes/header.php";
?>
<div class="card" style="max-width:720px;margin:0 auto;">
  <h1>Add shift</h1>
  <p class="muted">Enter your work shift details.</p>

  <?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>

  <form method="post">
    <div class="grid grid--2">
      <div>
        <label class="label">Date</label>
        <input class="input" type="date" name="shift_date" value="<?= h($_POST['shift_date'] ?? date('Y-m-d')) ?>" required>
      </div>

      <div>
        <label class="label">Job (optional)</label>
        <select class="input" name="job_id" id="job_id">
          <option value="" data-rate="">— None —</option>

          <?php foreach ($jobs as $j): ?>
            <option
              value="<?= (int)$j['id'] ?>"
              data-rate="<?= h((string)$j['default_rate']) ?>"
              <?= (($_POST['job_id'] ?? "") == $j['id']) ? "selected" : "" ?>>
              <?= h($j['job_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="label">Start time</label>
        <input class="input" id="start_time" type="time" name="start_time" value="<?= h($_POST['start_time'] ?? "09:00") ?>" required>
      </div>

      <div>
        <label class="label">End time</label>
        <input class="input" id="end_time" type="time" name="end_time" value="<?= h($_POST['end_time'] ?? "17:00") ?>" required>
        <div class="muted small" style="margin-top:6px;">Overnight shifts are handled automatically.</div>
      </div>

      <div>
        <label class="label">Break minutes</label>
        <input class="input" id="break_minutes" type="number" min="0" name="break_minutes" value="<?= h($_POST['break_minutes'] ?? "0") ?>">
      </div>

      <div>
        <label class="label">Hourly rate (£)</label>
        <input class="input" id="hourly_rate" type="number" step="0.01" min="0" name="hourly_rate" value="<?= h($_POST['hourly_rate'] ?? (string)$defaultRate) ?>">
      </div>
    </div>

    <label class="label">Notes (optional)</label>
    <input class="input" name="notes" value="<?= h($_POST['notes'] ?? "") ?>" placeholder="e.g., extra tasks, location, etc.">

    <div class="hr"></div>

    <div class="grid grid--2">
      <div class="card" style="padding:14px;">
        <div class="muted small">Preview hours</div>
        <div style="font-size:26px;font-weight:850;" id="preview_hours">0:00</div>
      </div>
      <div class="card" style="padding:14px;">
        <div class="muted small">Preview pay</div>
        <div style="font-size:26px;font-weight:850;" id="preview_pay">£0.00</div>
      </div>
    </div>

    <div class="btn-row" style="margin-top:14px;">
      <button class="btn" type="submit">Save shift</button>
      <a class="btn btn--ghost" href="dashboard.php">Cancel</a>
    </div>
  </form>
</div>

<?php require __DIR__ . "/includes/footer.php"; ?>