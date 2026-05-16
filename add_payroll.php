<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Generate Payroll';
$employees = db()->fetchAll('SELECT e.id, e.full_name, e.employee_code FROM employees e ORDER BY e.full_name');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $emp_id   = (int)($_POST['employee_id'] ?? 0);
        $month    = (int)($_POST['month'] ?? 0);
        $year     = (int)($_POST['year'] ?? 0);
        $basic    = (float)($_POST['basic_salary'] ?? 0);
        $hra      = (float)($_POST['hra'] ?? 0);
        $allow    = (float)($_POST['allowances'] ?? 0);
        $bonus    = (float)($_POST['bonus'] ?? 0);
        $incent   = (float)($_POST['incentives'] ?? 0);
        $ot       = (float)($_POST['overtime_pay'] ?? 0);
        $tax      = (float)($_POST['tax_deduction'] ?? 0);
        $pf       = (float)($_POST['pf_deduction'] ?? 0);
        $ins      = (float)($_POST['insurance_deduction'] ?? 0);
        $status   = $_POST['status'] ?? 'pending';
        $remarks  = trim($_POST['remarks'] ?? '');

        if (!$emp_id) $errors[] = 'Select an employee.';
        if (!$month || $month < 1 || $month > 12) $errors[] = 'Select a valid month.';
        if (!$year || $year < 2000) $errors[] = 'Enter a valid year.';
        if ($basic <= 0) $errors[] = 'Basic salary must be greater than 0.';

        if (!$errors) {
            $exists = db()->fetchOne('SELECT id FROM payroll WHERE employee_id=? AND month=? AND year=?', 'iii', [$emp_id, $month, $year]);
            if ($exists) $errors[] = 'Payroll for this employee and period already exists.';
        }

        if (!$errors) {
            db()->execute(
                'INSERT INTO payroll (employee_id,month,year,basic_salary,hra,allowances,bonus,incentives,overtime_pay,tax_deduction,pf_deduction,insurance_deduction,status,remarks)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                'iiidddddddddss',
                [$emp_id,$month,$year,$basic,$hra,$allow,$bonus,$incent,$ot,$tax,$pf,$ins,$status,$remarks]
            );

            // Notify employee
            $empUser = db()->fetchOne('SELECT user_id FROM employees WHERE id=?', 'i', [$emp_id]);
            if ($empUser) {
                addNotification($empUser['user_id'], 'Payroll Generated',
                    'Your payroll for ' . monthName($month) . ' ' . $year . ' has been generated.', 'success');
            }

            setFlash('success', 'Payroll generated successfully.');
            redirect(BASE_URL . 'admin/payroll.php');
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="main-wrapper">
  <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <div class="page-content">
    <div class="page-header">
      <div><h1>Generate Payroll</h1><p>Create a new payroll record</p></div>
      <a href="<?= BASE_URL ?>admin/payroll.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header"><i class="bi bi-plus-circle-fill me-2"></i>Payroll Details</div>
          <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
              <?= csrfField() ?>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Employee <span class="text-danger">*</span></label>
                  <select class="form-select" name="employee_id" required>
                    <option value="">Select Employee</option>
                    <?php foreach ($employees as $e): ?>
                      <option value="<?= $e['id'] ?>" <?= ($_POST['employee_id'] ?? '') == $e['id'] ? 'selected' : '' ?>>
                        <?= sanitize($e['employee_code']) ?> - <?= sanitize($e['full_name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Month <span class="text-danger">*</span></label>
                  <select class="form-select" name="month" required>
                    <option value="">Month</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                      <option value="<?= $m ?>" <?= ($_POST['month'] ?? date('n')) == $m ? 'selected' : '' ?>><?= monthName($m) ?></option>
                    <?php endfor; ?>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Year <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" name="year" value="<?= sanitize($_POST['year'] ?? date('Y')) ?>" min="2000" max="2099" required>
                </div>

                <div class="col-12"><div class="divider"></div><strong>Earnings</strong></div>
                <div class="col-md-4">
                  <label class="form-label">Basic Salary</label>
                  <input type="number" class="form-control" id="basic_salary" name="basic_salary" step="0.01" min="0" value="<?= sanitize($_POST['basic_salary'] ?? '0') ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">HRA</label>
                  <input type="number" class="form-control" id="hra" name="hra" step="0.01" min="0" value="<?= sanitize($_POST['hra'] ?? '0') ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Allowances</label>
                  <input type="number" class="form-control" id="allowances" name="allowances" step="0.01" min="0" value="<?= sanitize($_POST['allowances'] ?? '0') ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Bonus</label>
                  <input type="number" class="form-control" id="bonus" name="bonus" step="0.01" min="0" value="<?= sanitize($_POST['bonus'] ?? '0') ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Incentives</label>
                  <input type="number" class="form-control" id="incentives" name="incentives" step="0.01" min="0" value="<?= sanitize($_POST['incentives'] ?? '0') ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Overtime Pay</label>
                  <input type="number" class="form-control" id="overtime_pay" name="overtime_pay" step="0.01" min="0" value="<?= sanitize($_POST['overtime_pay'] ?? '0') ?>">
                </div>

                <div class="col-12"><div class="divider"></div><strong>Deductions</strong></div>
                <div class="col-md-4">
                  <label class="form-label">Tax Deduction</label>
                  <input type="number" class="form-control" id="tax_deduction" name="tax_deduction" step="0.01" min="0" value="<?= sanitize($_POST['tax_deduction'] ?? '0') ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">PF Deduction</label>
                  <input type="number" class="form-control" id="pf_deduction" name="pf_deduction" step="0.01" min="0" value="<?= sanitize($_POST['pf_deduction'] ?? '0') ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Insurance Deduction</label>
                  <input type="number" class="form-control" id="insurance_deduction" name="insurance_deduction" step="0.01" min="0" value="<?= sanitize($_POST['insurance_deduction'] ?? '0') ?>">
                </div>

                <div class="col-md-4">
                  <label class="form-label">Status</label>
                  <select class="form-select" name="status">
                    <option value="pending" <?= ($_POST['status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="paid" <?= ($_POST['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="cancelled" <?= ($_POST['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                  </select>
                </div>
                <div class="col-md-8">
                  <label class="form-label">Remarks</label>
                  <input type="text" class="form-control" name="remarks" value="<?= sanitize($_POST['remarks'] ?? '') ?>">
                </div>
              </div>
              <div class="mt-4">
                <button type="submit" class="btn btn-primary me-2"><i class="bi bi-check-circle me-1"></i>Generate Payroll</button>
                <a href="<?= BASE_URL ?>admin/payroll.php" class="btn btn-outline-secondary">Cancel</a>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Live Calculation -->
      <div class="col-lg-4">
        <div class="card" style="position:sticky;top:80px;">
          <div class="card-header"><i class="bi bi-calculator me-2"></i>Live Calculation</div>
          <div class="card-body">
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #e2e8f0;">
              <span class="text-muted">Gross Salary</span>
              <strong id="calc_gross">₹0.00</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #e2e8f0;">
              <span class="text-muted">Total Deductions</span>
              <strong class="text-danger" id="calc_deductions">₹0.00</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:14px 0;background:#eff6ff;border-radius:8px;padding:12px;margin-top:8px;">
              <span style="font-weight:700;">Net Salary</span>
              <strong style="font-size:18px;color:#2563eb;" id="calc_net">₹0.00</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
