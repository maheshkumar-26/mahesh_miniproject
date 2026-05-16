<?php
require_once __DIR__ . '/../config/config.php';
requireEmployee();

$pageTitle = 'Payroll History';
$emp = currentEmployee();
$empId = (int)$emp['id'];

// Payslip view
if (isset($_GET['payslip'])) {
    $pid = (int)$_GET['payslip'];
    $slip = db()->fetchOne(
        'SELECT p.*, e.full_name, e.employee_code, e.designation, d.name as department
         FROM payroll p
         JOIN employees e ON p.employee_id = e.id
         JOIN departments d ON e.department_id = d.id
         WHERE p.id=? AND p.employee_id=?',
        'ii', [$pid, $empId]
    );
    if ($slip):
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payslip - <?= monthName($slip['month']) ?> <?= $slip['year'] ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Arial', sans-serif; background: #f1f5f9; }
    .payslip-card { max-width: 700px; margin: 30px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.1); }
    .payslip-header { background: linear-gradient(135deg,#0f172a,#1e3a5f); color: #fff; padding: 28px 32px; }
    .payslip-body { padding: 28px 32px; }
    .payslip-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
    .payslip-total { background: #eff6ff; border-radius: 8px; padding: 14px 16px; margin-top: 12px; display: flex; justify-content: space-between; font-weight: 700; font-size: 18px; }
    @media print { .no-print { display: none; } body { background: #fff; } }
  </style>
</head>
<body>
  <div class="payslip-card">
    <div class="payslip-header">
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
          <h2 style="margin:0;font-size:22px;">Employee Compensation Insights</h2>
          <p style="margin:4px 0 0;opacity:.7;font-size:13px;">Payslip for <?= monthName($slip['month']) ?> <?= $slip['year'] ?></p>
        </div>
        <div style="text-align:right;">
          <div style="font-size:13px;opacity:.7;">Status</div>
          <div style="font-weight:700;font-size:16px;text-transform:capitalize;"><?= $slip['status'] ?></div>
        </div>
      </div>
    </div>
    <div class="payslip-body">
      <div class="row mb-4">
        <div class="col-6">
          <div style="font-size:12px;color:#64748b;">Employee Name</div>
          <div style="font-weight:700;"><?= sanitize($slip['full_name']) ?></div>
        </div>
        <div class="col-6">
          <div style="font-size:12px;color:#64748b;">Employee Code</div>
          <div style="font-weight:700;"><?= sanitize($slip['employee_code']) ?></div>
        </div>
        <div class="col-6 mt-2">
          <div style="font-size:12px;color:#64748b;">Designation</div>
          <div><?= sanitize($slip['designation']) ?></div>
        </div>
        <div class="col-6 mt-2">
          <div style="font-size:12px;color:#64748b;">Department</div>
          <div><?= sanitize($slip['department']) ?></div>
        </div>
      </div>

      <div class="row">
        <div class="col-6">
          <h6 style="font-weight:700;color:#16a34a;margin-bottom:12px;">EARNINGS</h6>
          <div class="payslip-row"><span>Basic Salary</span><span>₹<?= number_format($slip['basic_salary'],2) ?></span></div>
          <div class="payslip-row"><span>HRA</span><span>₹<?= number_format($slip['hra'],2) ?></span></div>
          <div class="payslip-row"><span>Allowances</span><span>₹<?= number_format($slip['allowances'],2) ?></span></div>
          <div class="payslip-row"><span>Bonus</span><span>₹<?= number_format($slip['bonus'],2) ?></span></div>
          <div class="payslip-row"><span>Incentives</span><span>₹<?= number_format($slip['incentives'],2) ?></span></div>
          <div class="payslip-row"><span>Overtime Pay</span><span>₹<?= number_format($slip['overtime_pay'],2) ?></span></div>
          <div class="payslip-row" style="font-weight:700;color:#16a34a;"><span>Gross Salary</span><span>₹<?= number_format($slip['gross_salary'],2) ?></span></div>
        </div>
        <div class="col-6">
          <h6 style="font-weight:700;color:#dc2626;margin-bottom:12px;">DEDUCTIONS</h6>
          <div class="payslip-row"><span>Tax</span><span>₹<?= number_format($slip['tax_deduction'],2) ?></span></div>
          <div class="payslip-row"><span>PF</span><span>₹<?= number_format($slip['pf_deduction'],2) ?></span></div>
          <div class="payslip-row"><span>Insurance</span><span>₹<?= number_format($slip['insurance_deduction'],2) ?></span></div>
          <div class="payslip-row" style="font-weight:700;color:#dc2626;"><span>Total Deductions</span><span>₹<?= number_format($slip['total_deductions'],2) ?></span></div>
        </div>
      </div>

      <div class="payslip-total">
        <span>NET TAKE-HOME SALARY</span>
        <span style="color:#2563eb;">₹<?= number_format($slip['net_salary'],2) ?></span>
      </div>

      <?php if ($slip['remarks']): ?>
        <div style="margin-top:16px;padding:12px;background:#f8fafc;border-radius:8px;font-size:13px;">
          <strong>Remarks:</strong> <?= sanitize($slip['remarks']) ?>
        </div>
      <?php endif; ?>

      <div class="mt-4 no-print" style="display:flex;gap:10px;">
        <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-1"></i>Print Payslip</button>
        <a href="<?= BASE_URL ?>employee/payroll_history.php" class="btn btn-outline-secondary">Back</a>
      </div>
    </div>
  </div>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</body>
</html>
<?php
    exit;
    endif;
}

// List all payroll
$records = db()->fetchAll(
    'SELECT * FROM payroll WHERE employee_id=? ORDER BY year DESC, month DESC',
    'i', [$empId]
);

include __DIR__ . '/../includes/header.php';
?>
<div class="main-wrapper">
  <?php include __DIR__ . '/../includes/employee_sidebar.php'; ?>
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <div class="page-content">
    <div class="page-header">
      <div><h1>Payroll History</h1><p>All your payroll records</p></div>
    </div>

    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Period</th>
                <th>Gross Salary</th>
                <th>Deductions</th>
                <th>Net Salary</th>
                <th>Status</th>
                <th>Payslip</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($records)): ?>
                <tr><td colspan="6" class="empty-state"><i class="bi bi-receipt d-block"></i>No payroll records yet</td></tr>
              <?php else: ?>
                <?php foreach ($records as $r): ?>
                  <tr>
                    <td><strong><?= monthName($r['month']) ?> <?= $r['year'] ?></strong></td>
                    <td><?= formatCurrency($r['gross_salary']) ?></td>
                    <td class="text-danger">-<?= formatCurrency($r['total_deductions']) ?></td>
                    <td><strong style="color:#2563eb;"><?= formatCurrency($r['net_salary']) ?></strong></td>
                    <td><span class="badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                    <td>
                      <a href="?payslip=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-file-earmark-text me-1"></i>View
                      </a>
                    </td>
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
<?php include __DIR__ . '/../includes/footer.php'; ?>
