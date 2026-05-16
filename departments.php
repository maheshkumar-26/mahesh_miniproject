<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Departments';
$errors = [];

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid request.');
        redirect(BASE_URL . 'admin/departments.php');
    }

    if ($_POST['action'] === 'add') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if (!$name) {
            $errors[] = 'Department name is required.';
        } else {
            db()->execute('INSERT INTO departments (name, description) VALUES (?,?)', 'ss', [$name, $desc]);
            setFlash('success', 'Department added successfully.');
            redirect(BASE_URL . 'admin/departments.php');
        }
    }

    if ($_POST['action'] === 'edit') {
        $did  = (int)$_POST['dept_id'];
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($name) {
            db()->execute('UPDATE departments SET name=?,description=? WHERE id=?', 'ssi', [$name, $desc, $did]);
            setFlash('success', 'Department updated.');
        }
        redirect(BASE_URL . 'admin/departments.php');
    }

    if ($_POST['action'] === 'delete') {
        $did = (int)$_POST['dept_id'];
        $empCount = db()->fetchOne('SELECT COUNT(*) as cnt FROM employees WHERE department_id=?', 'i', [$did])['cnt'] ?? 0;
        if ($empCount > 0) {
            setFlash('danger', 'Cannot delete: ' . $empCount . ' employee(s) are in this department.');
        } else {
            db()->execute('DELETE FROM departments WHERE id=?', 'i', [$did]);
            setFlash('success', 'Department deleted.');
        }
        redirect(BASE_URL . 'admin/departments.php');
    }
}

$departments = db()->fetchAll(
    'SELECT d.*, COUNT(e.id) as emp_count 
     FROM departments d 
     LEFT JOIN employees e ON d.id = e.department_id 
     GROUP BY d.id 
     ORDER BY d.name'
);

include __DIR__ . '/../includes/header.php';
?>
<div class="main-wrapper">
  <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <div class="page-content">
    <?php $flash = getFlash(); if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show auto-hide">
        <?= sanitize($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="page-header">
      <div><h1>Departments</h1><p>Manage company departments</p></div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeptModal">
        <i class="bi bi-plus-circle-fill me-2"></i>Add Department
      </button>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <div class="row g-3">
      <?php foreach ($departments as $d): ?>
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                  <h5 style="font-weight:700;margin-bottom:4px;"><?= sanitize($d['name']) ?></h5>
                  <p class="text-muted" style="font-size:13px;margin-bottom:8px;"><?= sanitize($d['description'] ?: 'No description') ?></p>
                  <span class="badge bg-primary"><?= $d['emp_count'] ?> Employee<?= $d['emp_count'] != 1 ? 's' : '' ?></span>
                </div>
                <div style="display:flex;gap:6px;">
                  <button class="btn btn-sm btn-outline-primary btn-icon"
                          data-bs-toggle="modal" data-bs-target="#editDeptModal"
                          data-id="<?= $d['id'] ?>" data-name="<?= sanitize($d['name']) ?>" data-desc="<?= sanitize($d['description']) ?>"
                          title="Edit">
                    <i class="bi bi-pencil-fill"></i>
                  </button>
                  <form method="POST" style="display:inline;">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="dept_id" value="<?= $d['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger btn-icon"
                            data-confirm="Delete department '<?= sanitize($d['name']) ?>'?" title="Delete">
                      <i class="bi bi-trash-fill"></i>
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Add Department</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Department</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editDeptModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Edit Department</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="dept_id" id="editDeptId">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="name" id="editDeptName" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" id="editDeptDesc" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$extraJs = '<script>
document.getElementById("editDeptModal").addEventListener("show.bs.modal", function(e) {
  var btn = e.relatedTarget;
  document.getElementById("editDeptId").value   = btn.getAttribute("data-id");
  document.getElementById("editDeptName").value  = btn.getAttribute("data-name");
  document.getElementById("editDeptDesc").value  = btn.getAttribute("data-desc");
});
</script>';
include __DIR__ . '/../includes/footer.php';
?>
