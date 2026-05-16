<?php
require_once __DIR__ . '/../config/config.php';
requireEmployee();

$pageTitle = 'Salary Breakdown';
$emp = currentEmployee();
$empId = (int)$emp['id'];

// Get latest payroll
$payroll = db()->fetchOne(
    'SELECT * FROM payroll WHERE employee_id=? ORDER BY year DESC, month DESC LIMIT 1',
    'i', [$empId]
);

include __DIR__ . '/../includes/header.php';
?>
<div class="main-wrapper">
  <?php include __DIR__ . '/../includes/employee_sidebar.php'; ?>
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <div class="page-content">
    <div class="page-header">
      <div><h1>Salary Breakdown</h1><p>Detailed view of your latest compensation</p></div>
    </div>

    <?php if (!$payroll): ?>
      <div class="card"><div class="card-body empty-state"><i class="bi bi-pie-chart d-block"></i>No payroll data available yet.</div></div>
    <?php else: ?>
      <div style="text-align:center;margin-bottom:8px;color:#64748b;font-size:13px;">
        Showing payroll for <strong><?= monthName($payroll['month']) ?> <?= $payroll['year'] ?></strong>
        &nbsp;<span class="badge-<?= $payroll['status'] ?>"><?= ucfirst($payroll['status']) ?></span>
      </div>

      <div class="row g-4">
        <!-- Earnings -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header" style="color:#16a34a;"><i class="bi bi-plus-circle-fill me-2"></i>Earnings</div>
            <div class="card-body">
              <?php
              $gross = (float)$payroll['gross_salary'];
              $earnings = [
                ['Basic Salary',  $payroll['basic_salary'],  'bar-blue'],
                ['HRA',           $payroll['hra'],            'bar-green'],
                ['Allowances',    $payroll['allowances'],     'bar-yellow'],
                ['Bonus',         $payroll['bonus'],          'bar-purple'],
                ['Incentives',    $payroll['incentives'],     'bar-cyan'],
                ['Overtime Pay',  $payroll['overtime_pay'],   'bar-red'],
              ];
              foreach ($earnings as $item):
                $pct = $gross > 0 ? round(($item[1] / $gross) * 100, 1) : 0;
              ?>
                <div class="salary-bar-wrap">
                  <div class="salary-bar-label">
                    <span><?= $item[0] ?></span>
                    <span><strong><?= formatCurrency($item[1]) ?></strong> <small class="text-muted">(<?= $pct ?>%)</small></span>
                  </div>
                  <div class="salary-bar-track">
                    <div class="salary-bar-fill <?= $item[2] ?>" data-width="<?= $pct ?>"></div>
                  </div>
                </div>
              <?php endforeach; ?>
              <div class="divider"></div>
              <div style="display:flex;justify-content:space-between;font-weight:700;">
                <span>Gross Salary</span>
                <span style="color:#16a34a;"><?= formatCurrency($gross) ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Deductions -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header" style="color:#dc2626;"><i class="bi bi-dash-circle-fill me-2"></i>Deductions</div>
            <div class="card-body">
              <?php
              $totalDed = (float)$payroll['total_deductions'];
              $deductions = [
                ['Tax Deduction',       $payroll['tax_deduction'],       'bar-red'],
                ['PF Deduction',        $payroll['pf_deduction'],        'bar-yellow'],
                ['Insurance Deduction', $payroll['insurance_deduction'], 'bar-purple'],
              ];
              foreach ($deductions as $item):
                $pct = $totalDed > 0 ? round(($item[1] / $totalDed) * 100, 1) : 0;
              ?>
                <div class="salary-bar-wrap">
                  <div class="salary-bar-label">
                    <span><?= $item[0] ?></span>
                    <span><strong><?= formatCurrency($item[1]) ?></strong> <small class="text-muted">(<?= $pct ?>%)</small></span>
                  </div>
                  <div class="salary-bar-track">
                    <div class="salary-bar-fill <?= $item[2] ?>" data-width="<?= $pct ?>"></div>
                  </div>
                </div>
              <?php endforeach; ?>
              <div class="divider"></div>
              <div style="display:flex;justify-content:space-between;font-weight:700;">
                <span>Total Deductions</span>
                <span style="color:#dc2626;">-<?= formatCurrency($totalDed) ?></span>
              </div>
            </div>
          </div>

          <!-- Net Salary Card -->
          <div class="card mt-4" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;">
            <div class="card-body text-center py-4">
              <div style="font-size:14px;opacity:.8;margin-bottom:8px;">Net Take-Home Salary</div>
              <div style="font-size:40px;font-weight:800;"><?= formatCurrency($payroll['net_salary']) ?></div>
              <div style="font-size:13px;opacity:.7;margin-top:8px;"><?= monthName($payroll['month']) ?> <?= $payroll['year'] ?></div>
            </div>
          </div>
        </div>

        <!-- Doughnut Chart -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header"><i class="bi bi-pie-chart-fill me-2"></i>Earnings vs Deductions</div>
            <div class="card-body"><canvas id="breakdownChart" height="260"></canvas></div>
          </div>
        </div>

        <!-- Summary Table -->
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header"><i class="bi bi-table me-2"></i>Summary</div>
            <div class="card-body p-0">
              <table class="table mb-0">
                <tbody>
                  <tr><td class="text-muted">Basic Salary</td><td class="text-end fw-600"><?= formatCurrency($payroll['basic_salary']) ?></td></tr>
                  <tr><td class="text-muted">HRA</td><td class="text-end fw-600"><?= formatCurrency($payroll['hra']) ?></td></tr>
                  <tr><td class="text-muted">Allowances</td><td class="text-end fw-600"><?= formatCurrency($payroll['allowances']) ?></td></tr>
                  <tr><td class="text-muted">Bonus</td><td class="text-end fw-600"><?= formatCurrency($payroll['bonus']) ?></td></tr>
                  <tr><td class="text-muted">Incentives</td><td class="text-end fw-600"><?= formatCurrency($payroll['incentives']) ?></td></tr>
                  <tr><td class="text-muted">Overtime Pay</td><td class="text-end fw-600"><?= formatCurrency($payroll['overtime_pay']) ?></td></tr>
                  <tr style="background:#f0fdf4;"><td><strong>Gross Salary</strong></td><td class="text-end fw-700 text-success"><?= formatCurrency($payroll['gross_salary']) ?></td></tr>
                  <tr><td class="text-muted">Tax</td><td class="text-end text-danger">-<?= formatCurrency($payroll['tax_deduction']) ?></td></tr>
                  <tr><td class="text-muted">PF</td><td class="text-end text-danger">-<?= formatCurrency($payroll['pf_deduction']) ?></td></tr>
                  <tr><td class="text-muted">Insurance</td><td class="text-end text-danger">-<?= formatCurrency($payroll['insurance_deduction']) ?></td></tr>
                  <tr style="background:#eff6ff;"><td><strong>Net Salary</strong></td><td class="text-end fw-700" style="color:#2563eb;font-size:16px;"><?= formatCurrency($payroll['net_salary']) ?></td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php
$extraJs = $payroll ? '<script>
document.addEventListener("DOMContentLoaded", function() {
  initDoughnutChart("breakdownChart",
    ["Gross Earnings","Total Deductions"],
    [' . $payroll['gross_salary'] . ',' . $payroll['total_deductions'] . '],
    ["#2563eb","#dc2626"]
  );
});
</script>' : '';
include __DIR__ . '/../includes/footer.php';
?>
