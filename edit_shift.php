<?php

declare(strict_types=1);
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";
require_login();

$page_title = "Edit shift";
$uid = current_user_id();
$error = "";

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM shifts WHERE id=? AND user_id=?");
$stmt->execute([$id, $uid]);
$shift = $stmt->fetch();

if (!$shift) {
  http_response_code(404);
  echo "Shift not found.";
  exit;
}

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
  } else {
    [$mins, $payStr] = calculate_minutes_and_pay($start_time, $end_time, $break_minutes, $hourly_rate);

    $up = $pdo->prepare("
      UPDATE shifts
      SET job_id=?, shift_date=?, start_time=?, end_time=?, break_minutes=?, hourly_rate=?, notes=?, computed_minutes=?, computed_pay=?
      WHERE id=? AND user_id=?
    ");
    $up->execute([
      $job_id,
      $shift_date,
      $start_time,
      $end_time,
      $break_minutes,
      $hourly_rate,
      $notes !== "" ? $notes : null,
      $mins,
      $payStr,
      $id,
      $uid
    ]);

    header("Location: dashboard.php");
    exit;
  }
}

require __DIR__ . "/includes/header.php";
?>
<div class="card" style="max-width:720px;margin:0 auto;">
  <h1>Edit shift</h1>
  <?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>

  <form method="post">
    <div class="grid grid--2">
      <div>
        <label class="label">Date</label>
        <input class="input" type="date" name="shift_date" value="<?= h($_POST['shift_date'] ?? $shift['shift_date']) ?>" required>
      </div>

      <div>
        <label class="label">Job (optional)</label>
        <select class="input" name="job_id" id="job_id">
          <option value="" data-rate="">— None —</option>

          <?php foreach ($jobs as $j): ?>
            <?php
            $current = $_POST['job_id'] ?? ($shift['job_id'] ?? "");
            $selected = ((string)$current === (string)$j['id']) ? "selected" : "";
            ?>
            <option
              value="<?= (int)$j['id'] ?>"
              data-rate="<?= h((string)($j['default_rate'] ?? 0)) ?>"
              <?= $selected ?>>
              <?= h($j['job_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>

      </div>

      <div>
        <label class="label">Start time</label>
        <input class="input" id="start_time" type="time" name="start_time" value="<?= h($_POST['start_time'] ?? substr($shift['start_time'], 0, 5)) ?>" required>
      </div>

      <div>
        <label class="label">End time</label>
        <input class="input" id="end_time" type="time" name="end_time" value="<?= h($_POST['end_time'] ?? substr($shift['end_time'], 0, 5)) ?>" required>
      </div>

      <div>
        <label class="label">Break minutes</label>
        <input class="input" id="break_minutes" type="number" min="0" name="break_minutes" value="<?= h($_POST['break_minutes'] ?? (string)$shift['break_minutes']) ?>">
      </div>

      <div>
        <label class="label">Hourly rate (£)</label>
        <input class="input" id="hourly_rate" type="number" step="0.01" min="0" name="hourly_rate" value="<?= h($_POST['hourly_rate'] ?? (string)$shift['hourly_rate']) ?>">
      </div>
    </div>

    <label class="label">Notes (optional)</label>
    <input class="input" name="notes" value="<?= h($_POST['notes'] ?? ($shift['notes'] ?? "")) ?>">

    <div class="hr"></div>

    <div class="grid grid--2">
      <div class="card" style="padding:14px;">
        <div class="muted small">Preview hours</div>
        <div style="font-size:26px;font-weight:850;" id="preview_hours"><?= h(minutes_to_hhmm((int)$shift['computed_minutes'])) ?></div>
      </div>
      <div class="card" style="padding:14px;">
        <div class="muted small">Preview pay</div>
        <div style="font-size:26px;font-weight:850;" id="preview_pay">£<?= h(number_format((float)$shift['computed_pay'], 2)) ?></div>
      </div>
    </div>

    <div class="btn-row" style="margin-top:14px;">
      <button class="btn" type="submit">Update shift</button>
      <a class="btn btn--ghost" href="dashboard.php">Back</a>
    </div>
  </form>
</div>
<?php require __DIR__ . "/includes/footer.php"; ?>