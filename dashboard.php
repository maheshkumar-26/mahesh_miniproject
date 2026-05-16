<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Admin Dashboard';

// Stats
$totalEmployees = db()->fetchOne('SELECT COUNT(*) as cnt FROM employees')['cnt'] ?? 0;
$totalDepartments = db()->fetchOne('SELECT COUNT(*) as cnt FROM departments')['cnt'] ?? 0;

$currentMonth = (int)date('n');
$currentYear = (int)date('Y');
$monthlyPayroll = db()->fetchOne(
    'SELECT SUM(net_salary) as total FROM payroll WHERE month=? AND year=?',
    'ii', [$currentMonth, $currentYear]
)['total'] ?? 0;

$pendingPayrolls = db()->fetchOne(
    'SELECT COUNT(*) as cnt FROM payroll WHERE status="pending"'
)['cnt'] ?? 0;

// Recent payroll
$recentPayroll = db()->fetchAll(
    'SELECT p.*, e.full_name, e.employee_code 
     FROM payroll p 
     JOIN employees e ON p.employee_id = e.id 
     ORDER BY p.created_at DESC LIMIT 5'
);

// Monthly payroll trend (last 6 months)
$trendData = [];
for ($i = 5; $i >= 0; $i--) {
    $m = (int)date('n', strtotime("-$i months"));
    $y = (int)date('Y', strtotime("-$i months"));
    $total = db()->fetchOne(
        'SELECT SUM(net_salary) as total FROM payroll WHERE month=? AND year=?',
        'ii', [$m, $y]
    )['total'] ?? 0;
    $trendData[] = ['month' => monthName($m) . ' ' . $y, 'total' => $total];
}

// Employees per department
$deptData = db()->fetchAll(
    'SELECT d.name, COUNT(e.id) as cnt 
     FROM departments d 
     LEFT JOIN employees e ON d.id = e.department_id 
     GROUP BY d.id'
);

// Recent employees
$recentEmployees = db()->fetchAll(
    'SELECT e.*, d.name as department_name 
     FROM employees e 
     JOIN departments d ON e.department_id = d.id 
     ORDER BY e.created_at DESC LIMIT 5'
);

include __DIR__ . '/../includes/header.php';
?>

<div class="main-wrapper">
  <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
  <?php include __DIR__ . '/../includes/topbar.php'; ?>

  <div class="page-content">
    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show auto-hide" role="alert">
        <?= sanitize($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="page-header">
      <div>
        <h1>Dashboard</h1>
        <p>Welcome back, <?= sanitize($_SESSION['user_name']) ?>!</p>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="stat-card fade-in-up">
          <div class="stat-card-inner">
            <div>
              <div class="stat-value"><?= $totalEmployees ?></div>
              <div class="stat-label">Total Employees</div>
            </div>
            <div class="stat-icon stat-icon-blue">
              <i class="bi bi-people-fill"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card fade-in-up delay-1">
          <div class="stat-card-inner">
            <div>
              <div class="stat-value"><?= formatCurrency($monthlyPayroll) ?></div>
              <div class="stat-label">Payroll This Month</div>
            </div>
            <div class="stat-icon stat-icon-green">
              <i class="bi bi-cash-stack"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card fade-in-up delay-2">
          <div class="stat-card-inner">
            <div>
              <div class="stat-value"><?= $totalDepartments ?></div>
              <div class="stat-label">Departments</div>
            </div>
            <div class="stat-icon stat-icon-purple">
              <i class="bi bi-building"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card fade-in-up delay-3">
          <div class="stat-card-inner">
            <div>
              <div class="stat-value"><?= $pendingPayrolls ?></div>
              <div class="stat-label">Pending Payrolls</div>
            </div>
            <div class="stat-icon stat-icon-yellow">
              <i class="bi bi-clock-history"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Monthly Payroll Trend -->
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header">
            <i class="bi bi-graph-up me-2"></i>Monthly Payroll Trend
          </div>
          <div class="card-body">
            <canvas id="trendChart" height="280"></canvas>
          </div>
        </div>
      </div>

      <!-- Employees per Department -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header">
            <i class="bi bi-pie-chart me-2"></i>Employees by Department
          </div>
          <div class="card-body">
            <canvas id="deptChart" height="280"></canvas>
          </div>
        </div>
      </div>

      <!-- Recent Payroll -->
      <div class="col-lg-7">
        <div class="card">
          <div class="card-header">
            <i class="bi bi-clock-history me-2"></i>Recent Payroll
            <a href="<?= BASE_URL ?>admin/payroll.php" class="btn btn-sm btn-primary">View All</a>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>Employee</th>
                    <th>Period</th>
                    <th>Net Salary</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($recentPayroll)): ?>
                    <tr><td colspan="4" class="text-center text-muted">No payroll records yet</td></tr>
                  <?php else: ?>
                    <?php foreach ($recentPayroll as $p): ?>
                      <tr>
                        <td>
                          <strong><?= sanitize($p['full_name']) ?></strong><br>
                          <small class="text-muted"><?= sanitize($p['employee_code']) ?></small>
                        </td>
                        <td><?= monthName($p['month']) ?> <?= $p['year'] ?></td>
                        <td><strong><?= formatCurrency($p['net_salary']) ?></strong></td>
                        <td><span class="badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Employees -->
      <div class="col-lg-5">
        <div class="card">
          <div class="card-header">
            <i class="bi bi-person-plus me-2"></i>Recent Employees
            <a href="<?= BASE_URL ?>admin/employees.php" class="btn btn-sm btn-primary">View All</a>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Department</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($recentEmployees)): ?>
                    <tr><td colspan="2" class="text-center text-muted">No employees yet</td></tr>
                  <?php else: ?>
                    <?php foreach ($recentEmployees as $e): ?>
                      <tr>
                        <td>
                          <strong><?= sanitize($e['full_name']) ?></strong><br>
                          <small class="text-muted"><?= sanitize($e['employee_code']) ?></small>
                        </td>
                        <td><?= sanitize($e['department_name']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
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
  // Trend Chart
  const trendLabels = ' . json_encode(array_column($trendData, 'month')) . ';
  const trendValues = ' . json_encode(array_column($trendData, 'total')) . ';
  initLineChart("trendChart", trendLabels, [{
    label: "Net Payroll",
    data: trendValues,
    borderColor: "#2563eb",
    backgroundColor: "rgba(37,99,235,0.1)",
    fill: true
  }]);

  // Department Chart
  const deptLabels = ' . json_encode(array_column($deptData, 'name')) . ';
  const deptValues = ' . json_encode(array_column($deptData, 'cnt')) . ';
  const deptColors = ["#2563eb","#16a34a","#d97706","#dc2626","#7c3aed","#0891b2"];
  initDoughnutChart("deptChart", deptLabels, deptValues, deptColors);
});
</script>';
include __DIR__ . '/../includes/footer.php';
?>
