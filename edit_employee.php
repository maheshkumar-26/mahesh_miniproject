<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Edit Employee';
$departments = db()->fetchAll('SELECT * FROM departments ORDER BY name');
$errors = [];

$id = (int)($_GET['id'] ?? 0);
$emp = db()->fetchOne('SELECT e.*, u.email as user_email FROM employees e JOIN users u ON e.user_id=u.id WHERE e.id=?', 'i', [$id]);
if (!$emp) { setFlash('danger', 'Employee not found.'); redirect(BASE_URL . 'admin/employees.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $full_name   = trim($_POST['full_name'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $gender      = $_POST['gender'] ?? 'Male';
        $dept_id     = (int)($_POST['department_id'] ?? 0);
        $designation = trim($_POST['designation'] ?? '');
        $joining     = $_POST['joining_date'] ?? '';
        $exp         = (float)($_POST['experience_years'] ?? 0);
        $address     = trim($_POST['address'] ?? '');
        $new_password = $_POST['new_password'] ?? '';

        if (!$full_name) $errors[] = 'Full name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (!$dept_id) $errors[] = 'Department is required.';

        if (!$errors) {
            $existing = db()->fetchOne('SELECT id FROM users WHERE email=? AND id!=?', 'si', [$email, $emp['user_id']]);
            if ($existing) $errors[] = 'Email already in use by another account.';
        }

        if (!$errors) {
            $profileImg = $emp['profile_image'];
            if (!empty($_FILES['profile_image']['name'])) {
                $profileImg = uploadImage($_FILES['profile_image'], $emp['profile_image']);
            }

            db()->execute(
                'UPDATE employees SET full_name=?,email=?,phone=?,gender=?,department_id=?,designation=?,joining_date=?,experience_years=?,address=?,profile_image=? WHERE id=?',
                'ssssisssssi',
                [$full_name, $email, $phone, $gender, $dept_id, $designation, $joining ?: null, $exp, $address, $profileImg, $id]
            );

            db()->execute('UPDATE users SET name=?,email=? WHERE id=?', 'ssi', [$full_name, $email, $emp['user_id']]);

            if ($new_password && strlen($new_password) >= 6) {
                $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                db()->execute('UPDATE users SET password=? WHERE id=?', 'si', [$hashed, $emp['user_id']]);
            }

            setFlash('success', 'Employee updated successfully.');
            redirect(BASE_URL . 'admin/employees.php');
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
      <div>
        <h1>Edit Employee</h1>
        <p><?= sanitize($emp['full_name']) ?> &mdash; <?= sanitize($emp['employee_code']) ?></p>
      </div>
      <a href="<?= BASE_URL ?>admin/employees.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
      </a>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header"><i class="bi bi-pencil-fill me-2"></i>Edit Information</div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
          <?= csrfField() ?>
          <div class="row g-3">
            <div class="col-md-2 text-center">
              <img src="<?= profileImageUrl($emp['profile_image'] ?? 'default.png') ?>" class="profile-avatar mb-2" alt="">
              <div class="form-text">Current photo</div>
            </div>
            <div class="col-md-10">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Full Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="full_name" value="<?= sanitize($_POST['full_name'] ?? $emp['full_name']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" name="email" value="<?= sanitize($_POST['email'] ?? $emp['email']) ?>" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Phone</label>
                  <input type="text" class="form-control" name="phone" value="<?= sanitize($_POST['phone'] ?? $emp['phone']) ?>">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Gender</label>
                  <select class="form-select" name="gender">
                    <?php foreach (['Male','Female','Other'] as $g): ?>
                      <option value="<?= $g ?>" <?= ($_POST['gender'] ?? $emp['gender']) === $g ? 'selected' : '' ?>><?= $g ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Department <span class="text-danger">*</span></label>
                  <select class="form-select" name="department_id" required>
                    <?php foreach ($departments as $d): ?>
                      <option value="<?= $d['id'] ?>" <?= ($_POST['department_id'] ?? $emp['department_id']) == $d['id'] ? 'selected' : '' ?>><?= sanitize($d['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Designation</label>
              <input type="text" class="form-control" name="designation" value="<?= sanitize($_POST['designation'] ?? $emp['designation']) ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Joining Date</label>
              <input type="date" class="form-control" name="joining_date" value="<?= sanitize($_POST['joining_date'] ?? $emp['joining_date']) ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Experience (Years)</label>
              <input type="number" class="form-control" name="experience_years" step="0.1" min="0" value="<?= sanitize($_POST['experience_years'] ?? $emp['experience_years']) ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Address</label>
              <textarea class="form-control" name="address" rows="2"><?= sanitize($_POST['address'] ?? $emp['address']) ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">New Password <span class="text-muted">(leave blank to keep current)</span></label>
              <input type="password" class="form-control" name="new_password" minlength="6">
            </div>
            <div class="col-md-6">
              <label class="form-label">Change Profile Image</label>
              <input type="file" class="form-control" name="profile_image" accept="image/*">
            </div>
          </div>
          <div class="mt-4">
            <button type="submit" class="btn btn-primary me-2"><i class="bi bi-check-circle me-1"></i>Save Changes</button>
            <a href="<?= BASE_URL ?>admin/employees.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
