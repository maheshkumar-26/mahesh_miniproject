<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Employees';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid request.');
    } else {
        $deleteId = (int)$_POST['delete_id'];
        $emp = db()->fetchOne('SELECT user_id FROM employees WHERE id=?', 'i', [$deleteId]);
        if ($emp) {
            db()->execute('DELETE FROM users WHERE id=?', 'i', [$emp['user_id']]);
            setFlash('success', 'Employee deleted successfully.');
        }
    }
    redirect(BASE_URL . 'admin/employees.php');
}

// Filters
$search = trim($_GET['search'] ?? '');
$deptFilter = (int)($_GET['dept'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

// Build query
$where = '1=1';
$params = [];
$types = '';

if ($search) {
    $where .= ' AND (e.full_name LIKE ? OR e.employee_code LIKE ? OR e.email LIKE ?)';
    $s = '%' . $search . '%';
    $params = array_merge($params, [$s, $s, $s]);
    $types .= 'sss';
}
if ($deptFilter) {
    $where .= ' AND e.department_id=?';
    $params[] = $deptFilter;
    $types .= 'i';
}

$totalRow = db()->fetchOne(
    "SELECT COUNT(*) as cnt FROM employees e WHERE $where",
    $types, $params
);
$total = (int)($totalRow['cnt'] ?? 0);
$pagination = paginate($total, $perPage, $page, BASE_URL . 'admin/employees.php?search=' . urlencode($search) . '&dept=' . $deptFilter . '&page=');

$employees = db()->fetchAll(
    "SELECT e.*, d.name as department_name 
     FROM employees e 
     JOIN departments d ON e.department_id = d.id 
     WHERE $where 
     ORDER BY e.created_at DESC 
     LIMIT ? OFFSET ?",
    $types . 'ii',
    array_merge($params, [$perPage, $pagination['offset']])
);

$departments = db()->fetchAll('SELECT * FROM departments ORDER BY name');

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
        <h1>Employees</h1>
        <p>Manage all employees (<?= $total ?> total)</p>
      </div>
      <a href="<?= BASE_URL ?>admin/add_employee.php" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-2"></i>Add Employee
      </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
          <div class="col-md-5">
            <label class="form-label">Search</label>
            <input type="text" class="form-control" name="search" value="<?= sanitize($search) ?>" placeholder="Name, code or email...">
          </div>
          <div class="col-md-4">
            <label class="form-label">Department</label>
            <select class="form-select" name="dept">
              <option value="">All Departments</option>
              <?php foreach ($departments as $d): ?>
                <option value="<?= $d['id'] ?>" <?= $deptFilter == $d['id'] ? 'selected' : '' ?>>
                  <?= sanitize($d['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-primary me-2">
              <i class="bi bi-search me-1"></i>Filter
            </button>
            <a href="<?= BASE_URL ?>admin/employees.php" class="btn btn-outline-secondary">Reset</a>
          </div>
        </form>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Code</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Joining Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($employees)): ?>
                <tr>
                  <td colspan="6" class="empty-state">
                    <i class="bi bi-people d-block"></i>
                    No employees found
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($employees as $e): ?>
                  <tr>
                    <td><span class="badge bg-light text-dark fw-600"><?= sanitize($e['employee_code']) ?></span></td>
                    <td>
                      <div style="display:flex;align-items:center;gap:10px;">
                        <img src="<?= profileImageUrl($e['profile_image'] ?? 'default.png') ?>" 
                             alt="" style="width:34px;height:34px;border-radius:50%;object-fit:cover;">
                        <div>
                          <strong><?= sanitize($e['full_name']) ?></strong><br>
                          <small class="text-muted"><?= sanitize($e['email']) ?></small>
                        </div>
                      </div>
                    </td>
                    <td><?= sanitize($e['department_name']) ?></td>
                    <td><?= sanitize($e['designation'] ?? '-') ?></td>
                    <td><?= $e['joining_date'] ? date('d M Y', strtotime($e['joining_date'])) : '-' ?></td>
                    <td>
                      <div style="display:flex;gap:6px;">
                        <a href="<?= BASE_URL ?>admin/edit_employee.php?id=<?= $e['id'] ?>" 
                           class="btn btn-sm btn-outline-primary btn-icon" title="Edit">
                          <i class="bi bi-pencil-fill"></i>
                        </a>
                        <form method="POST" style="display:inline;">
                          <?= csrfField() ?>
                          <input type="hidden" name="delete_id" value="<?= $e['id'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger btn-icon"
                                  data-confirm="Delete <?= sanitize($e['full_name']) ?>? This cannot be undone."
                                  title="Delete">
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

    <!-- Pagination -->
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
