<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Payroll';

// Mark as paid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { setFlash('danger', 'Invalid request.'); redirect(BASE_URL . 'admin/payroll.php'); }
    $pid = (int)$_POST['payroll_id'];
    db()->execute('UPDATE payroll SET status="paid" WHERE id=?', 'i', [$pid]);
    setFlash('success', 'Payroll marked as paid.');
    redirect(BASE_URL . 'admin/payroll.php');
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { setFlash('danger', 'Invalid request.'); redirect(BASE_URL . 'admin/payroll.php'); }
    db()->execute('DELETE FROM payroll WHERE id=?', 'i', [(int)$_POST['delete_id']]);
    setFlash('success', 'Payroll record deleted.');
    redirect(BASE_URL . 'admin/payroll.php');
}

// Filters
$filterMonth  = (int)($_GET['month'] ?? 0);
$filterYear   = (int)($_GET['year'] ?? 0);
$filterStatus = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$where = '1=1'; $params = []; $types = '';
if ($filterMonth) { $where .= ' AND p.month=?'; $params[] = $filterMonth; $types .= 'i'; }
if ($filterYear)  { $where .= ' AND p.year=?';  $params[] = $filterYear;  $types .= 'i'; }
if ($filterStatus){ $where .= ' AND p.status=?'; $params[] = $filterStatus; $types .= 's'; }

$total = (int)(db()->fetchOne("SELECT COUNT(*) as cnt FROM payroll p WHERE $where", $types, $params)['cnt'] ?? 0);
$pagination = paginate($total, $perPage, $page, BASE_URL . 'admin/payroll.php?month='.$filterMonth.'&year='.$filterYear.'&status='.urlencode($filterStatus).'&page=');

$records = db()->fetchAll(
    "SELECT p.*, e.full_name, e.employee_code 
     FROM payroll p JOIN employees e ON p.employee_id=e.id 
     WHERE $where ORDER BY p.year DESC, p.month DESC, p.id DESC 
     LIMIT ? OFFSET ?",
    $types . 'ii', array_merge($params, [$perPage, $pagination['offset']])
);

include __DIR__ . '/../includes/header.php';
?>
<div class="main-wrapper">
  <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <div class="page-content">
    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show auto-hide">
        <?= sanitize($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="page-header">
      <div><h1>Payroll</h1><p>Manage all payroll records (<?= $total ?> total)</p></div>
      <a href="<?= BASE_URL ?>admin/add_payroll.php" class="btn btn-primary">
        <i class="bi bi-plus-circle-fill me-2"></i>Generate Payroll
      </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label">Month</label>
            <select class="form-select" name="month">
              <option value="">All Months</option>
              <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $filterMonth == $m ? 'selected' : '' ?>><?= monthName($m) ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Year</label>
            <select class="form-select" name="year">
              <option value="">All Years</option>
              <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                <option value="<?= $y ?>" <?= $filterYear == $y ? 'selected' : '' ?>><?= $y ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <option value="">All</option>
              <option value="paid" <?= $filterStatus === 'paid' ? 'selected' : '' ?>>Paid</option>
              <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
              <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-primary me-2"><i class="bi bi-search me-1"></i>Filter</button>
            <a href="<?= BASE_URL ?>admin/payroll.php" class="btn btn-outline-secondary">Reset</a>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Period</th>
                <th>Gross</th>
                <th>Deductions</th>
                <th>Net Salary</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($records)): ?>
                <tr><td colspan="7" class="empty-state"><i class="bi bi-cash-stack d-block"></i>No payroll records found</td></tr>
              <?php else: ?>
                <?php foreach ($records as $r): ?>
                  <tr>
                    <td>
                      <strong><?= sanitize($r['full_name']) ?></strong><br>
                      <small class="text-muted"><?= sanitize($r['employee_code']) ?></small>
                    </td>
                    <td><?= monthName($r['month']) ?> <?= $r['year'] ?></td>
                    <td><?= formatCurrency($r['gross_salary']) ?></td>
                    <td class="text-danger">-<?= formatCurrency($r['total_deductions']) ?></td>
                    <td><strong><?= formatCurrency($r['net_salary']) ?></strong></td>
                    <td><span class="badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                    <td>
                      <div style="display:flex;gap:4px;flex-wrap:wrap;">
                        <?php if ($r['status'] === 'pending'): ?>
                          <form method="POST" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="mark_paid" value="1">
                            <input type="hidden" name="payroll_id" value="<?= $r['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-success" title="Mark Paid">
                              <i class="bi bi-check-circle me-1"></i>Mark Paid
                            </button>
                          </form>
                        <?php endif; ?>
                        <form method="POST" style="display:inline;">
                          <?= csrfField() ?>
                          <input type="hidden" name="delete_id" value="<?= $r['id'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger btn-icon"
                                  data-confirm="Delete this payroll record?" title="Delete">
                            <i class="bi bi-trash-fill"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <?php if ($pagination['total_pages'] > 1): ?>
      <nav class="mt-3">
        <ul class="pagination justify-content-center">
          <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
              <a class="page-link" href="<?= $pagination['url'] . $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
