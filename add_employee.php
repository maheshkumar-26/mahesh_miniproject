<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Add Employee';
$departments = db()->fetchAll('SELECT * FROM departments ORDER BY name');
$errors = [];

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
        $password    = $_POST['password'] ?? '';

        if (!$full_name) $errors[] = 'Full name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (!$dept_id) $errors[] = 'Department is required.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

        // Check duplicate email
        if (!$errors) {
            $existing = db()->fetchOne('SELECT id FROM users WHERE email=?', 's', [$email]);
            if ($existing) $errors[] = 'Email already exists.';
        }

        if (!$errors) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            db()->execute(
                'INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)',
                'ssss', [$full_name, $email, $hashed, 'employee']
            );
            $userId = db()->lastInsertId();

            $empCode = generateEmployeeCode();
            $profileImg = 'default.png';
            if (!empty($_FILES['profile_image']['name'])) {
                $profileImg = uploadImage($_FILES['profile_image']);
            }

            db()->execute(
                'INSERT INTO employees (user_id, employee_code, full_name, email, phone, gender, department_id, designation, joining_date, experience_years, address, profile_image)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
                'issssssissss',
                [$userId, $empCode, $full_name, $email, $phone, $gender, $dept_id, $designation, $joining ?: null, $exp, $address, $profileImg]
            );

            setFlash('success', 'Employee ' . $full_name . ' added successfully!');
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
        <h1>Add Employee</h1>
        <p>Create a new employee account</p>
      </div>
      <a href="<?= BASE_URL ?>admin/employees.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
      </a>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header"><i class="bi bi-person-plus-fill me-2"></i>Employee Information</div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
          <?= csrfField() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="full_name" value="<?= sanitize($_POST['full_name'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email Address <span class="text-danger">*</span></label>
              <input type="email" class="form-control" name="email" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Phone</label>
              <input type="text" class="form-control" name="phone" value="<?= sanitize($_POST['phone'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Gender</label>
              <select class="form-select" name="gender">
                <option value="Male" <?= ($_POST['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= ($_POST['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= ($_POST['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Department <span class="text-danger">*</span></label>
              <select class="form-select" name="department_id" required>
                <option value="">Select Department</option>
                <?php foreach ($departments as $d): ?>
                  <option value="<?= $d['id'] ?>" <?= ($_POST['department_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                    <?= sanitize($d['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Designation</label>
              <input type="text" class="form-control" name="designation" value="<?= sanitize($_POST['designation'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Joining Date</label>
              <input type="date" class="form-control" name="joining_date" value="<?= sanitize($_POST['joining_date'] ?? '') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Experience (Years)</label>
              <input type="number" class="form-control" name="experience_years" step="0.1" min="0" value="<?= sanitize($_POST['experience_years'] ?? '0') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Address</label>
              <textarea class="form-control" name="address" rows="2"><?= sanitize($_POST['address'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Password <span class="text-danger">*</span></label>
              <input type="password" class="form-control" name="password" required minlength="6">
              <div class="form-text">Minimum 6 characters</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Profile Image</label>
              <input type="file" class="form-control" name="profile_image" accept="image/*">
              <div class="form-text">Max 2MB. JPG, PNG, GIF, WEBP</div>
            </div>
          </div>
          <div class="mt-4">
            <button type="submit" class="btn btn-primary me-2">
              <i class="bi bi-check-circle me-1"></i>Add Employee
            </button>
            <a href="<?= BASE_URL ?>admin/employees.php" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
