<?php
require_once __DIR__ . '/../config/config.php';
requireEmployee();

$pageTitle = 'Compensation Insights';
$emp = currentEmployee();
$empId = (int)$emp['id'];
$deptId = (int)$emp['department_id'];

// All payroll records
$allPayroll = db()->fetchAll(
    'SELECT * FROM payroll WHERE employee_id=? ORDER BY year ASC, month ASC',
    'i', [$empId]
);

// YTD summary
$ytdData = db()->fetchOne(
    'SELECT SUM(net_salary) as net, SUM(gross_salary) as gross, SUM(total_deductions) as deductions,
            SUM(bonus) as bonus, SUM(incentives) as incentives, COUNT(*) as months
     FROM payroll WHERE employee_id=? AND year=? AND status="paid"',
    'ii', [$empId, (int)date('Y')]
);

// Avg salary this employee vs department
$myAvg = db()->fetchOne(
    'SELECT AVG(net_salary) as avg FROM payroll WHERE employee_id=? AND status="paid"',
    'i', [$empId]
)['avg'] ?? 0;

$deptAvg = db()->fetchOne(
    'SELECT AVG(p.net_salary) as avg FROM payroll p
     JOIN employees e ON p.employee_id = e.id
     WHERE e.department_id=? AND p.status="paid"',
    'i', [$deptId]
)['avg'] ?? 0;

// Salary growth
$first = db()->fetchOne('SELECT net_salary FROM payroll WHERE employee_id=? ORDER BY year ASC, month ASC LIMIT 1', 'i', [$empId]);
$last  = db()->fetchOne('SELECT net_salary FROM payroll WHERE employee_id=? ORDER BY year DESC, month DESC LIMIT 1', 'i', [$empId]);
$growth = ($first && $last && $first['net_salary'] > 0)
    ? round((($last['net_salary'] - $first['net_salary']) / $first['net_salary']) * 100, 1)
    : 0;

// Trend labels/values
$trendLabels = array_map(fn($r) => monthName($r['month']) . ' ' . $r['year'], $allPayroll);
$trendValues = array_map(fn($r) => (float)$r['net_salary'], $allPayroll);
$bonusValues = array_map(fn($r) => (float)$r['bonus'] + (float)$r['incentives'], $allPayroll);

include __DIR__ . '/../includes/header.php';
?>
<div class="main-wrapper">
  <?php include __DIR__ . '/../includes/employee_sidebar.php'; ?>
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <div class="page-content">
    <div class="page-header">
      <div><h1>Compensation Insights</h1><p>Analytics and trends for your compensation</p></div>
    </div>

    <!-- Insight Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="insight-card fade-in-up">
          <div class="insight-value"><?= formatCurrency($ytdData['net'] ?? 0) ?></div>
          <div class="insight-label">YTD Net Earnings (<?= date('Y') ?>)</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="insight-card fade-in-up delay-1">
          <div class="insight-value"><?= formatCurrency($myAvg) ?></div>
          <div class="insight-label">Your Avg Net Salary</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="insight-card fade-in-up delay-2">
          <div class="insight-value"><?= formatCurrency($ytdData['bonus'] ?? 0) ?></div>
          <div class="insight-label">YTD Bonus + Incentives</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="insight-card fade-in-up delay-3">
          <div class="insight-value" style="color:<?= $growth >= 0 ? '#16a34a' : '#dc2626' ?>;">
            <?= $growth >= 0 ? '+' : '' ?><?= $growth ?>%
          </div>
          <div class="insight-label">Overall Salary Growth</div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Net Salary Trend -->
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header"><i class="bi bi-graph-up-arrow me-2"></i>Net Salary Trend (All Time)</div>
          <div class="card-body"><canvas id="trendChart" height="260"></canvas></div>
        </div>
      </div>

      <!-- My Avg vs Dept Avg -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header"><i class="bi bi-bar-chart-fill me-2"></i>You vs Department Avg</div>
          <div class="card-body"><canvas id="compChart" height="260"></canvas></div>
        </div>
      </div>

      <!-- Bonus & Incentives Trend -->
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header"><i class="bi bi-gift-fill me-2"></i>Bonus & Incentives Trend</div>
          <div class="card-body"><canvas id="bonusChart" height="240"></canvas></div>
        </div>
      </div>

      <!-- YTD Summary Table -->
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header"><i class="bi bi-table me-2"></i>YTD Summary (<?= date('Y') ?>)</div>
          <div class="card-body p-0">
            <table class="table mb-0">
              <tbody>
                <tr><td class="text-muted">Months Paid</td><td class="text-end fw-600"><?= $ytdData['months'] ?? 0 ?></td></tr>
                <tr><td class="text-muted">Gross Earnings</td><td class="text-end fw-600"><?= formatCurrency($ytdData['gross'] ?? 0) ?></td></tr>
                <tr><td class="text-muted">Total Deductions</td><td class="text-end fw-600 text-danger"><?= formatCurrency($ytdData['deductions'] ?? 0) ?></td></tr>
                <tr><td class="text-muted">Net Earnings</td><td class="text-end fw-600 text-success"><?= formatCurrency($ytdData['net'] ?? 0) ?></td></tr>
                <tr><td class="text-muted">Bonus</td><td class="text-end fw-600"><?= formatCurrency($ytdData['bonus'] ?? 0) ?></td></tr>
                <tr><td class="text-muted">Incentives</td><td class="text-end fw-600"><?= formatCurrency($ytdData['incentives'] ?? 0) ?></td></tr>
                <tr style="background:#eff6ff;"><td><strong>Your Avg vs Dept Avg</strong></td>
                  <td class="text-end fw-700" style="color:<?= $myAvg >= $deptAvg ? '#16a34a' : '#dc2626' ?>;">
                    <?= $myAvg >= $deptAvg ? '+' : '' ?><?= round((($myAvg - $deptAvg) / max($deptAvg, 1)) * 100, 1) ?>%
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$extraJs = '<script>
document.addEventListener("DOMContentLoaded", function() {
  initLineChart("trendChart",
    ' . json_encode($trendLabels) . ',
    [{ label: "Net Salary", data: ' . json_encode($trendValues) . ',
       borderColor: "#2563eb", backgroundColor: "rgba(37,99,235,0.1)", fill: true }]
  );
  initBarChart("compChart",
    ["Your Avg", "Dept Avg"],
    [{ label: "Net Salary", data: [' . round($myAvg, 2) . ',' . round($deptAvg, 2) . '],
       backgroundColor: ["#2563eb","#94a3b8"] }]
  );
  initBarChart("bonusChart",
    ' . json_encode($trendLabels) . ',
    [{ label: "Bonus + Incentives", data: ' . json_encode($bonusValues) . ',
       backgroundColor: "rgba(245,158,11,0.7)", borderColor: "#d97706", borderWidth: 1 }]
  );
});
</script>';
include __DIR__ . '/../includes/footer.php';
?>
