<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Reports';

// CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $data = db()->fetchAll(
        'SELECT e.employee_code, e.full_name, d.name as department, p.month, p.year,
                p.basic_salary, p.hra, p.allowances, p.bonus, p.incentives, p.overtime_pay,
                p.gross_salary, p.tax_deduction, p.pf_deduction, p.insurance_deduction,
                p.total_deductions, p.net_salary, p.status
         FROM payroll p
         JOIN employees e ON p.employee_id = e.id
         JOIN departments d ON e.department_id = d.id
         ORDER BY p.year DESC, p.month DESC'
    );
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="payroll_report_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Code','Name','Department','Month','Year','Basic','HRA','Allowances','Bonus','Incentives','OT Pay','Gross','Tax','PF','Insurance','Total Deductions','Net Salary','Status']);
    foreach ($data as $row) {
        fputcsv($out, [
            $row['employee_code'], $row['full_name'], $row['department'],
            monthName($row['month']), $row['year'],
            $row['basic_salary'], $row['hra'], $row['allowances'], $row['bonus'],
            $row['incentives'], $row['overtime_pay'], $row['gross_salary'],
            $row['tax_deduction'], $row['pf_deduction'], $row['insurance_deduction'],
            $row['total_deductions'], $row['net_salary'], $row['status']
        ]);
    }
    fclose($out);
    exit;
}

// Summary stats
$totalPaid    = db()->fetchOne('SELECT SUM(net_salary) as t FROM payroll WHERE status="paid"')['t'] ?? 0;
$totalPending = db()->fetchOne('SELECT SUM(net_salary) as t FROM payroll WHERE status="pending"')['t'] ?? 0;
$avgSalary    = db()->fetchOne('SELECT AVG(net_salary) as a FROM payroll WHERE status="paid"')['a'] ?? 0;
$totalRecords = db()->fetchOne('SELECT COUNT(*) as c FROM payroll')['c'] ?? 0;

// Monthly trend (last 12 months)
$trend = [];
for ($i = 11; $i >= 0; $i--) {
    $m = (int)date('n', strtotime("-$i months"));
    $y = (int)date('Y', strtotime("-$i months"));
    $t = db()->fetchOne('SELECT SUM(net_salary) as t FROM payroll WHERE month=? AND year=?', 'ii', [$m, $y])['t'] ?? 0;
    $trend[] = ['label' => date('M Y', strtotime("-$i months")), 'total' => (float)$t];
}

// Department-wise avg salary
$deptSalary = db()->fetchAll(
    'SELECT d.name, AVG(p.net_salary) as avg_sal
     FROM payroll p
     JOIN employees e ON p.employee_id = e.id
     JOIN departments d ON e.department_id = d.id
     WHERE p.status = "paid"
     GROUP BY d.id ORDER BY avg_sal DESC'
);

// Top 5 earners
$topEarners = db()->fetchAll(
    'SELECT e.full_name, e.employee_code, d.name as dept, MAX(p.net_salary) as max_sal
     FROM payroll p
     JOIN employees e ON p.employee_id = e.id
     JOIN departments d ON e.department_id = d.id
     WHERE p.status = "paid"
     GROUP BY e.id ORDER BY max_sal DESC LIMIT 5'
);

include __DIR__ . '/../includes/header.php';
?>
<div class="main-wrapper">
  <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <div class="page-content">
    <div class="page-header">
      <div><h1>Reports</h1><p>Payroll analytics and insights</p></div>
      <a href="<?= BASE_URL ?>admin/reports.php?export=csv" class="btn btn-success">
        <i class="bi bi-download me-2"></i>Export CSV
      </a>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="stat-card fade-in-up">
          <div class="stat-card-inner">
            <div><div class="stat-value"><?= formatCurrency($totalPaid) ?></div><div class="stat-label">Total Paid</div></div>
            <div class="stat-icon stat-icon-green"><i class="bi bi-check-circle-fill"></i></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card fade-in-up delay-1">
          <div class="stat-card-inner">
            <div><div class="stat-value"><?= formatCurrency($totalPending) ?></div><div class="stat-label">Total Pending</div></div>
            <div class="stat-icon stat-icon-yellow"><i class="bi bi-clock-fill"></i></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card fade-in-up delay-2">
          <div class="stat-card-inner">
            <div><div class="stat-value"><?= formatCurrency($avgSalary) ?></div><div class="stat-label">Avg Net Salary</div></div>
            <div class="stat-icon stat-icon-blue"><i class="bi bi-graph-up"></i></div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card fade-in-up delay-3">
          <div class="stat-card-inner">
            <div><div class="stat-value"><?= $totalRecords ?></div><div class="stat-label">Total Records</div></div>
            <div class="stat-icon stat-icon-purple"><i class="bi bi-file-earmark-text-fill"></i></div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Monthly Trend -->
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header"><i class="bi bi-graph-up me-2"></i>Monthly Payroll Trend (12 Months)</div>
          <div class="card-body"><canvas id="trendChart" height="280"></canvas></div>
        </div>
      </div>

      <!-- Department Avg Salary -->
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header"><i class="bi bi-bar-chart me-2"></i>Dept. Avg Salary</div>
          <div class="card-body"><canvas id="deptChart" height="280"></canvas></div>
        </div>
      </div>

      <!-- Top Earners -->
      <div class="col-12">
        <div class="card">
          <div class="card-header"><i class="bi bi-trophy-fill me-2"></i>Top 5 Earners</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr><th>#</th><th>Employee</th><th>Department</th><th>Highest Net Salary</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($topEarners as $i => $e): ?>
                    <tr>
                      <td>
                        <?php if ($i === 0): ?><i class="bi bi-trophy-fill text-warning"></i>
                        <?php elseif ($i === 1): ?><i class="bi bi-trophy-fill text-secondary"></i>
                        <?php elseif ($i === 2): ?><i class="bi bi-trophy-fill" style="color:#cd7f32;"></i>
                        <?php else: ?><?= $i + 1 ?><?php endif; ?>
                      </td>
                      <td><strong><?= sanitize($e['full_name']) ?></strong><br><small class="text-muted"><?= sanitize($e['employee_code']) ?></small></td>
                      <td><?= sanitize($e['dept']) ?></td>
                      <td><strong style="color:#2563eb;"><?= formatCurrency($e['max_sal']) ?></strong></td>
                    </tr>
                  <?php endforeach; ?>
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
  initLineChart("trendChart",
    ' . json_encode(array_column($trend, 'label')) . ',
    [{ label: "Net Payroll", data: ' . json_encode(array_column($trend, 'total')) . ',
       borderColor: "#2563eb", backgroundColor: "rgba(37,99,235,0.1)", fill: true }]
  );
  initBarChart("deptChart",
    ' . json_encode(array_column($deptSalary, 'name')) . ',
    [{ label: "Avg Net Salary", data: ' . json_encode(array_column($deptSalary, 'avg_sal')) . ',
       backgroundColor: ["#2563eb","#16a34a","#d97706","#dc2626","#7c3aed","#0891b2"] }]
  );
});
</script>';
include __DIR__ . '/../includes/footer.php';
?>
