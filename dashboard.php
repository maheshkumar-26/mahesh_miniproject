<?php
require_once __DIR__ . '/../config/config.php';
requireEmployee();

$pageTitle = 'My Dashboard';
$emp = currentEmployee();
$empId = (int)$emp['id'];

// Latest payroll
$latest = db()->fetchOne(
    'SELECT * FROM payroll WHERE employee_id=? ORDER BY year DESC, month DESC LIMIT 1',
    'i', [$empId]
);

// YTD earnings
$ytd = db()->fetchOne(
    'SELECT SUM(net_salary) as total FROM payroll WHERE employee_id=? AND year=? AND status="paid"',
    'ii', [$empId, (int)date('Y')]
)['total'] ?? 0;

// Pending count
$pending = db()->fetchOne(
    'SELECT COUNT(*) as cnt FROM payroll WHERE employee_id=? AND status="pending"',
    'i', [$empId]
)['cnt'] ?? 0;

// Salary trend (last 6 months)
$trend = [];
for ($i = 5; $i >= 0; $i--) {
    $m = (int)date('n', strtotime("-$i months"));
    $y = (int)date('Y', strtotime("-$i months"));
    $row = db()->fetchOne('SELECT net_salary FROM payroll WHERE employee_id=? AND month=? AND year=?', 'iii', [$empId, $m, $y]);
    $trend[] = ['label' => date('M Y', strtotime("-$i months")), 'value' => (float)($row['net_salary'] ?? 0)];
}

include __DIR__ . '/../includes/header.php';
?>
<div class="main-wrapper">
  <?php include __DIR__ . '/../includes/employee_sidebar.php'; ?>
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <div class="page-content">
    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show auto-hide">
        <?= sanitize($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="page-header">
      <div>
        <h1>Welcome, <?= sanitize($emp['full_name']) ?>!</h1>
        <p><?= sanitize($emp['designation'] ?? 'Employee') ?> &bull; <?= sanitize($emp['department_name']) ?> &bull; <?= sanitize($emp['employee_code']) ?></p>
      </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="stat-card fade-in-up">
          <div class="stat-card-inner">
            <div>
              <div class="stat-value"><?= $latest ? formatCurrency($latest['net_salary']) : '—' ?></div>
              <div class="stat-label">Latest Net Salary</div>
              <?php if ($latest): ?>
                <div class="stat-trend trend-up"><?= monthName($latest['month']) ?> <?= $latest['year'] ?></div>
              <?php endif; ?>
            </div>
            <div class="stat-icon stat-icon-blue"><i class="bi bi-wallet2"></i></div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card fade-in-up delay-1">
          <div class="stat-card-inner">
            <div>
              <div class="stat-value"><?= formatCurrency($ytd) ?></div>
              <div class="stat-label">YTD Earnings (<?= date('Y') ?>)</div>
            </div>
            <div class="stat-icon stat-icon-green"><i class="bi bi-graph-up-arrow"></i></div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card fade-in-up delay-2">
          <div class="stat-card-inner">
            <div>
              <div class="stat-value"><?= $pending ?></div>
              <div class="stat-label">Pending Payrolls</div>
            </div>
            <div class="stat-icon stat-icon-yellow"><i class="bi bi-clock-history"></i></div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Salary Trend Chart -->
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header"><i class="bi bi-graph-up me-2"></i>Salary Trend (Last 6 Months)</div>
          <div class="card-body"><canvas id="salaryTrend" height="260"></canvas></div>
        </div>
      </div>

      <!-- Latest Payslip Summary -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header"><i class="bi bi-receipt me-2"></i>Latest Payslip</div>
          <div class="card-body">
            <?php if ($latest): ?>
              <div style="text-align:center;margin-bottom:16px;">
                <div style="font-size:13px;color:#64748b;"><?= monthName($latest['month']) ?> <?= $latest['year'] ?></div>
                <div style="font-size:28px;font-weight:800;color:#2563eb;"><?= formatCurrency($latest['net_salary']) ?></div>
                <span class="badge-<?= $latest['status'] ?>"><?= ucfirst($latest['status']) ?></span>
              </div>
              <div class="divider"></div>
              <?php $items = [
                ['Gross Salary', $latest['gross_salary'], 'text-success'],
                ['Total Deductions', $latest['total_deductions'], 'text-danger'],
              ]; foreach ($items as $item): ?>
                <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;">
                  <span class="text-muted"><?= $item[0] ?></span>
                  <strong class="<?= $item[2] ?>"><?= formatCurrency($item[1]) ?></strong>
                </div>
              <?php endforeach; ?>
              <div class="mt-3">
                <a href="<?= BASE_URL ?>employee/payroll_history.php" class="btn btn-primary w-100 btn-sm">
                  <i class="bi bi-clock-history me-1"></i>View All Payslips
                </a>
              </div>
            <?php else: ?>
              <div class="empty-state"><i class="bi bi-receipt d-block"></i>No payroll records yet</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-12">
        <div class="card">
          <div class="card-header"><i class="bi bi-lightning-fill me-2"></i>Quick Links</div>
          <div class="card-body">
            <div class="row g-3">
              <?php $links = [
                ['Salary Breakdown', 'pie-chart-fill', 'salary_breakdown.php', 'stat-icon-blue'],
                ['Payroll History', 'clock-history', 'payroll_history.php', 'stat-icon-green'],
                ['Compensation Insights', 'graph-up-arrow', 'insights.php', 'stat-icon-purple'],
                ['Satisfaction Index', 'star-fill', 'feedback.php', 'stat-icon-yellow'],
                ['My Profile', 'person-circle', 'profile.php', 'stat-icon-cyan'],
              ]; foreach ($links as $l): ?>
                <div class="col-md-2 col-4">
                  <a href="<?= BASE_URL ?>employee/<?= $l[2] ?>" style="text-decoration:none;">
                    <div style="text-align:center;padding:16px 8px;border-radius:12px;border:1px solid #e2e8f0;transition:all .2s;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background=''">
                      <div class="stat-icon <?= $l[3] ?>" style="margin:0 auto 8px;"><i class="bi bi-<?= $l[1] ?>"></i></div>
                      <div style="font-size:12px;font-weight:600;color:#0f172a;"><?= $l[0] ?></div>
                    </div>
                  </a>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$extraJs = '<script>
document.addEventListener("DOMContentLoaded", function() {
  initLineChart("salaryTrend",
    ' . json_encode(array_column($trend, 'label')) . ',
    [{ label: "Net Salary", data: ' . json_encode(array_column($trend, 'value')) . ',
       borderColor: "#2563eb", backgroundColor: "rgba(37,99,235,0.1)", fill: true }]
  );
});
</script>';
include __DIR__ . '/../includes/footer.php';
?>
