<?php
declare(strict_types=1);
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/functions.php";
require_login();

$page_title = "Settings";
$uid = current_user_id();
$error = "";
$flash = "";

$userStmt = $pdo->prepare("SELECT name, email, default_rate, week_start FROM users WHERE id=?");
$userStmt->execute([$uid]);
$user = $userStmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (($_POST['action'] ?? '') === 'save_settings') {
    $default_rate = (float)($_POST['default_rate'] ?? 0);
    $week_start = ($_POST['week_start'] ?? 'mon') === 'sun' ? 'sun' : 'mon';

    $up = $pdo->prepare("UPDATE users SET default_rate=?, week_start=? WHERE id=?");
    $up->execute([$default_rate, $week_start, $uid]);
    $flash = "Settings saved.";
    $user['default_rate'] = $default_rate;
    $user['week_start'] = $week_start;
  }

  if (($_POST['action'] ?? '') === 'add_job') {
    $job_name = trim($_POST['job_name'] ?? "");
    $job_rate = (float)($_POST['job_rate'] ?? 0);
    if ($job_name === "") {
      $error = "Job name is required.";
    } else {
      $ins = $pdo->prepare("INSERT INTO jobs (user_id, job_name, default_rate) VALUES (?,?,?)");
      $ins->execute([$uid, $job_name, $job_rate]);
      $flash = "Job added.";
    }
  }
}

$jobsStmt = $pdo->prepare("SELECT id, job_name, default_rate FROM jobs WHERE user_id=? ORDER BY job_name ASC");
$jobsStmt->execute([$uid]);
$jobs = $jobsStmt->fetchAll();

require __DIR__ . "/includes/header.php";
?>
<div class="grid grid--2">
  <section class="card">
    <h1>Settings</h1>
    <p class="muted">Change default hourly rate and week start.</p>

    <?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>

    <form method="post">
      <input type="hidden" name="action" value="save_settings">
      <label class="label">Default hourly rate (£)</label>
      <input class="input" type="number" step="0.01" min="0" name="default_rate" value="<?= h((string)$user['default_rate']) ?>">

      <label class="label">Week starts on</label>
      <select class="input" name="week_start">
        <option value="mon" <?= $user['week_start']==='mon'?'selected':'' ?>>Monday</option>
        <option value="sun" <?= $user['week_start']==='sun'?'selected':'' ?>>Sunday</option>
      </select>

      <div class="btn-row" style="margin-top:14px;">
        <button class="btn" type="submit">Save settings</button>
      </div>
    </form>
  </section>

  <section class="card">
    <h2>Jobs (optional)</h2>
    <p class="muted">Add workplaces with their own rate.</p>

    <form method="post" class="grid" style="grid-template-columns: 1fr 160px; gap:10px; align-items:end;">
      <input type="hidden" name="action" value="add_job">
      <div>
        <label class="label">Job name</label>
        <input class="input" name="job_name" placeholder="e.g., Cafe Barking">
      </div>
      <div>
        <label class="label">Rate (£)</label>
        <input class="input" type="number" step="0.01" min="0" name="job_rate" placeholder="12.00">
      </div>
      <div style="grid-column:1 / -1;">
        <button class="btn" type="submit">Add job</button>
      </div>
    </form>

    <div class="hr"></div>

    <?php if (!$jobs): ?>
      <p class="muted">No jobs added yet.</p>
    <?php else: ?>
      <table class="table">
        <thead>
          <tr><th>Job</th><th class="right">Default rate</th></tr>
        </thead>
        <tbody>
          <?php foreach ($jobs as $j): ?>
            <tr>
              <td><?= h($j['job_name']) ?></td>
              <td class="right">£<?= h(number_format((float)$j['default_rate'],2)) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="muted small" style="margin-top:10px;">(Edit/delete jobs can be added next.)</p>
    <?php endif; ?>
  </section>
</div>
<?php require __DIR__ . "/includes/footer.php"; ?>
