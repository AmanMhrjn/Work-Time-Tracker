<?php
declare(strict_types=1);

function h(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/**
 * Calculate worked minutes (handles overnight) minus break.
 * Returns [workedMinutes, payDecimalString].
 */
function calculate_minutes_and_pay(string $startHHMM, string $endHHMM, int $breakMinutes, float $rate): array {
  $start = strtotime("1970-01-01 $startHHMM");
  $end   = strtotime("1970-01-01 $endHHMM");

  if ($start === false || $end === false) {
    return [0, "0.00"];
  }

  // Overnight shift
  if ($end <= $start) {
    $end = strtotime("1970-01-02 $endHHMM");
  }

  $totalMinutes = (int) round(($end - $start) / 60);

  $breakMinutes = max(0, $breakMinutes);
  $workedMinutes = max(0, $totalMinutes - $breakMinutes);

  $hours = $workedMinutes / 60.0;
  $pay = $hours * $rate;

  return [$workedMinutes, number_format($pay, 2, '.', '')];
}

/** Monday/Sunday week start */
function week_start_date(DateTimeImmutable $date, string $weekStart): DateTimeImmutable {
  $dow = (int)$date->format('N'); // 1=Mon..7=Sun
  if ($weekStart === 'sun') {
    $dow0 = (int)$date->format('w'); // 0=Sun..6=Sat
    return $date->modify("-{$dow0} days");
  }
  return $date->modify("-" . ($dow - 1) . " days");
}

function minutes_to_hhmm(int $minutes): string {
  $h = intdiv($minutes, 60);
  $m = $minutes % 60;
  return sprintf("%d:%02d", $h, $m);
}
