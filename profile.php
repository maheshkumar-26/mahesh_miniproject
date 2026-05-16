<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'My Profile';
$user = currentUser();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } elseif (isset($_POST['update_profile'])) {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if (!$name) $errors[] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
        if (!$errors) {
            $dup = db()->fetchOne('SELECT id FROM users WHERE email=? AND id!=?', 'si', [$email, $user['id']]);
            if ($dup) $errors[] = 'Email already in use.';
        }
        if (!$errors) {
            db()->execute('UPDATE users SET name=?,email=? WHERE id=?', 'ssi', [$name, $email, $user['id']]);
            $_SESSION['user_name']  = $name;
            $_SESSION['user_email'] = $email;
            $success = 'Profile updated successfully.';
            $user = currentUser();
        }
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
  <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <div class="page-content">
    <div class="page-header">
      <div><h1>My Profile</h1><p>Manage your account settings</p></div>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success auto-hide alert-dismissible fade show"><?= sanitize($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="card text-center">
          <div class="card-body py-4">
            <div class="profile-avatar-wrap d-inline-block mb-3">
              <img src="<?= BASE_URL ?>assets/images/default-avatar.png" class="profile-avatar" alt="Admin">
            </div>
            <h5 class="fw-700"><?= sanitize($user['name']) ?></h5>
            <p class="text-muted"><?= sanitize($user['email']) ?></p>
            <span class="badge bg-primary">Administrator</span>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <!-- Update Profile -->
        <div class="card mb-4">
          <div class="card-header"><i class="bi bi-person-fill me-2"></i>Update Profile</div>
          <div class="card-body">
            <form method="POST">
              <?= csrfField() ?>
              <input type="hidden" name="update_profile" value="1">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Full Name</label>
                  <input type="text" class="form-control" name="name" value="<?= sanitize($user['name']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email Address</label>
                  <input type="email" class="form-control" name="email" value="<?= sanitize($user['email']) ?>" required>
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
