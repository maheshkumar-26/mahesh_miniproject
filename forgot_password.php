<?php
require_once __DIR__ . '/../config/config.php';

if (isLoggedIn()) {
    redirect(BASE_URL . (isAdmin() ? 'admin' : 'employee') . '/dashboard.php');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $csrf = $_POST['csrf_token'] ?? '';

    if (!verifyCsrf($csrf)) {
        $error = 'Invalid request.';
    } elseif (empty($email)) {
        $error = 'Please enter your email address.';
    } else {
        $user = db()->fetchOne('SELECT id FROM users WHERE email=?', 's', [$email]);
        
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        db()->execute(
            'INSERT INTO password_reset (email, token, expires_at) VALUES (?,?,?)',
            'sss',
            [$email, $token, $expires]
        );
        
        // In production, send email here
        // For demo, show token
        if ($user) {
            $success = 'If this email exists in our system, a password reset link has been sent. <br><small class="text-muted">(Demo token: ' . $token . ')</small>';
        } else {
            $success = 'If this email exists in our system, a password reset link has been sent.';
        }
    }
}

$pageTitle = 'Forgot Password';
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
    <div class="logo-icon"><i class="bi bi-key-fill"></i></div>
    <h1>Forgot Password?</h1>
    <p>Enter your email to reset your password</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i><?= sanitize($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i><?= $success ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <?= csrfField() ?>
    
    <div class="mb-3">
      <label for="email" class="form-label">Email Address</label>
      <input type="email" class="form-control" id="email" name="email" 
             value="<?= sanitize($_POST['email'] ?? '') ?>" required autofocus>
      <div class="form-text">We'll send you a password reset link</div>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">
      <i class="bi bi-envelope me-2"></i>Send Reset Link
    </button>

    <div class="text-center">
      <a href="<?= BASE_URL ?>auth/login.php" class="text-decoration-none" style="font-size:13px;">
        <i class="bi bi-arrow-left me-1"></i>Back to Login
      </a>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
