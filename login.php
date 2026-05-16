<?php
require_once __DIR__ . '/../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . (isAdmin() ? 'admin' : 'employee') . '/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if (!verifyCsrf($csrf)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $user = db()->fetchOne('SELECT * FROM users WHERE email=? AND is_active=1', 's', [$email]);
        
        if ($user && password_verify($password, $user['password'])) {
            loginUser($user);
            setFlash('success', 'Welcome back, ' . sanitize($user['name']) . '!');
            redirect(BASE_URL . ($user['role'] === 'admin' ? 'admin' : 'employee') . '/dashboard.php');
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?> | <?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-page">

<div class="auth-card">
  <div class="auth-logo">
    <div class="logo-icon"><i class="bi bi-bar-chart-fill"></i></div>
    <h1><?= APP_NAME ?></h1>
    <p>Sign in to your account</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i><?= sanitize($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
      <?= sanitize($flash['message']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <?= csrfField() ?>
    
    <div class="mb-3">
      <label for="email" class="form-label">Email Address</label>
      <input type="email" class="form-control" id="email" name="email" 
             value="<?= sanitize($_POST['email'] ?? '') ?>" required autofocus>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" class="form-control" id="password" name="password" required>
    </div>

    <div class="mb-3 form-check">
      <input type="checkbox" class="form-check-input" id="remember" name="remember">
      <label class="form-check-label" for="remember">Remember me</label>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">
      <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
    </button>

    <div class="text-center">
      <a href="<?= BASE_URL ?>auth/forgot_password.php" class="text-decoration-none" style="font-size:13px;">
        Forgot your password?
      </a>
    </div>
  </form>

  <div class="mt-4 pt-3" style="border-top:1px solid #e2e8f0;text-align:center;font-size:12px;color:#64748b;">
    <strong>Demo Credentials:</strong><br>
    Admin: admin@eci.com / password<br>
    Employee: rahul@eci.com / password
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
