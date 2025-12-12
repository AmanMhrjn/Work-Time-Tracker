function toMinutes(hhmm) {
  const [h, m] = hhmm.split(":").map(Number);
  return (h * 60) + m;
}

function calcWorkedMinutes(start, end, breakMin) {
  if (!start || !end) return 0;
  let s = toMinutes(start);
  let e = toMinutes(end);
  if (e <= s) e += 24 * 60; // overnight
  const total = e - s;
  const b = Math.max(0, parseInt(breakMin || "0", 10));
  return Math.max(0, total - b);
}

function fmtHours(mins){
  const h = Math.floor(mins/60);
  const m = mins % 60;
  return `${h}:${String(m).padStart(2,'0')}`;
}

function updatePreview() {
  const start = document.querySelector("#start_time");
  const end = document.querySelector("#end_time");
  const br = document.querySelector("#break_minutes");
  const rate = document.querySelector("#hourly_rate");

  const outH = document.querySelector("#preview_hours");
  const outP = document.querySelector("#preview_pay");

  if (!start || !end || !br || !rate || !outH || !outP) return;

  const mins = calcWorkedMinutes(start.value, end.value, br.value);
  const r = parseFloat(rate.value || "0");
  const pay = (mins/60) * r;

  outH.textContent = fmtHours(mins);
  outP.textContent = "£" + pay.toFixed(2);
}

document.addEventListener("input", (e) => {
  if (["start_time","end_time","break_minutes","hourly_rate"].includes(e.target.id)) {
    updatePreview();
  }
});

document.addEventListener("DOMContentLoaded", updatePreview);

function applyJobRate() {
  const job = document.querySelector("#job_id");
  const rate = document.querySelector("#hourly_rate");
  if (!job || !rate) return;

  const opt = job.options[job.selectedIndex];
  const jobRate = opt?.dataset?.rate;

  // If job has a rate, auto-fill it
  if (jobRate !== undefined && jobRate !== "") {
    rate.value = parseFloat(jobRate).toFixed(2);
    updatePreview(); // refresh preview hours/pay
  }
}

document.addEventListener("change", (e) => {
  if (e.target.id === "job_id") {
    applyJobRate();
  }
});

document.addEventListener("DOMContentLoaded", () => {
  // if a job is already selected (edit page / post-back), apply once
  applyJobRate();
});
