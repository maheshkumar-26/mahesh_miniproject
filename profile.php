<?php
require_once __DIR__ . '/../config/config.php';
requireEmployee();

$pageTitle = 'My Profile';
$emp = currentEmployee();
$user = currentUser();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } elseif (isset($_POST['update_profile'])) {
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $profileImg = $emp['profile_image'];

        if (!empty($_FILES['profile_image']['name'])) {
            $profileImg = uploadImage($_FILES['profile_image'], $emp['profile_image']);
        }

        db()->execute(
            'UPDATE employees SET phone=?,address=?,profile_image=? WHERE id=?',
            'sssi', [$phone, $address, $profileImg, $emp['id']]
        );
        $success = 'Profile updated successfully.';
        $emp = currentEmployee();

    } elseif (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password'])) $errors[] = 'Current password is incorrect.';
        if (strlen($new) < 6) $errors[] = 'New password must be at least 6 characters.';
        if ($new !== $confirm) $errors[] = 'Passwords do not match.';

        if (!$errors) {
            db()->execute('UPDATE users SET password=? WHERE id=?', 'si', [password_hash($new, PASSWORD_BCRYPT), $user['id']]);
            $success = 'Password changed successfully.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="main-wrapper">
  <?php include __DIR__ . '/../includes/employee_sidebar.php'; ?>
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <div class="page-content">
    <div class="page-header">
      <div><h1>My Profile</h1><p>View and update your personal information</p></div>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show auto-hide"><?= sanitize($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row g-4">
      <!-- Profile Card -->
      <div class="col-md-4">
        <div class="card text-center">
          <div class="card-body py-4">
            <div class="profile-avatar-wrap d-inline-block mb-3">
              <img src="<?= profileImageUrl($emp['profile_image'] ?? 'default.png') ?>" class="profile-avatar" alt="Profile">
            </div>
            <h5 class="fw-700"><?= sanitize($emp['full_name']) ?></h5>
            <p class="text-muted mb-1"><?= sanitize($emp['designation'] ?? '') ?></p>
            <p class="text-muted mb-2" style="font-size:13px;"><?= sanitize($emp['department_name']) ?></p>
            <span class="badge bg-primary"><?= sanitize($emp['employee_code']) ?></span>
          </div>
        </div>

        <!-- Info Card -->
        <div class="card mt-3">
          <div class="card-header"><i class="bi bi-info-circle me-2"></i>Employee Info</div>
          <div class="card-body p-0">
            <table class="table mb-0" style="font-size:13px;">
              <tr><td class="text-muted">Email</td><td><?= sanitize($emp['email']) ?></td></tr>
              <tr><td class="text-muted">Phone</td><td><?= sanitize($emp['phone'] ?: '—') ?></td></tr>
              <tr><td class="text-muted">Gender</td><td><?= sanitize($emp['gender']) ?></td></tr>
              <tr><td class="text-muted">Joining</td><td><?= $emp['joining_date'] ? date('d M Y', strtotime($emp['joining_date'])) : '—' ?></td></tr>
              <tr><td class="text-muted">Experience</td><td><?= $emp['experience_years'] ?> yrs</td></tr>
            </table>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <!-- Update Profile -->
        <div class="card mb-4">
          <div class="card-header"><i class="bi bi-pencil-fill me-2"></i>Update Profile</div>
          <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
              <?= csrfField() ?>
              <input type="hidden" name="update_profile" value="1">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Phone</label>
                  <input type="text" class="form-control" name="phone" value="<?= sanitize($emp['phone'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Profile Image</label>
                  <input type="file" class="form-control" name="profile_image" accept="image/*">
                  <div class="form-text">Max 2MB. JPG, PNG, WEBP</div>
                </div>
                <div class="col-12">
                  <label class="form-label">Address</label>
                  <textarea class="form-control" name="address" rows="3"><?= sanitize($emp['address'] ?? '') ?></textarea>
                </div>
              </div>
              <div class="mt-3">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Save Changes</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Change Password -->
        <div class="card">
          <div class="card-header"><i class="bi bi-lock-fill me-2"></i>Change Password</div>
          <div class="card-body">
            <form method="POST">
              <?= csrfField() ?>
              <input type="hidden" name="change_password" value="1">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">Current Password</label>
                  <input type="password" class="form-control" name="current_password" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">New Password</label>
                  <input type="password" class="form-control" name="new_password" minlength="6" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Confirm New Password</label>
                  <input type="password" class="form-control" name="confirm_password" required>
                </div>
              </div>
              <div class="mt-3">
                <button type="submit" class="btn btn-warning"><i class="bi bi-key-fill me-1"></i>Change Password</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
