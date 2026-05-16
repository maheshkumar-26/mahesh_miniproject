<?php
require_once __DIR__ . '/config/config.php';

// Redirect if logged in
if (isLoggedIn()) {
    redirect(BASE_URL . (isAdmin() ? 'admin' : 'employee') . '/dashboard.php');
}

$pageTitle = 'Welcome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="landing-nav">
  <div style="display:flex;align-items:center;gap:10px;">
    <div class="brand-icon"><i class="bi bi-bar-chart-fill"></i></div>
    <span style="color:#fff;font-weight:700;font-size:18px;"><?= APP_NAME ?></span>
  </div>
  <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-primary btn-sm">
    <i class="bi bi-box-arrow-in-right me-1"></i> Login
  </a>
</nav>

<!-- Hero Section -->
<section class="landing-hero">
  <div class="container" style="position:relative;z-index:1;">
    <div class="hero-badge">
      <i class="bi bi-stars"></i> Modern Payroll Management
    </div>
    <h1 class="hero-title">
      Employee <span>Compensation</span><br>Insights
    </h1>
    <p class="hero-subtitle">
      Streamline payroll management, empower employees with transparent salary insights, and make data-driven compensation decisions.
    </p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>auth/login.php" class="btn btn-primary btn-lg">
        Get Started <i class="bi bi-arrow-right ms-2"></i>
      </a>
      <a href="#features" class="btn btn-outline-light btn-lg">
        Learn More
      </a>
    </div>
  </div>
</section>

<!-- Features Section -->
<section id="features" style="padding:80px 20px;background:#fff;">
  <div class="container">
    <div class="text-center mb-5">
      <h2 style="font-size:32px;font-weight:800;color:#0f172a;margin-bottom:12px;">
        Powerful Features
      </h2>
      <p style="color:#64748b;font-size:16px;">Everything you need to manage employee compensation effectively</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="feature-card" style="background:#fff;border:1px solid #e2e8f0;color:#0f172a;">
          <div class="feature-icon" style="color:#2563eb;">
            <i class="bi bi-cash-stack"></i>
          </div>
          <h3>Payroll Management</h3>
          <p>Generate, track, and manage employee payroll with automated calculations and detailed breakdowns.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card" style="background:#fff;border:1px solid #e2e8f0;color:#0f172a;">
          <div class="feature-icon" style="color:#16a34a;">
            <i class="bi bi-graph-up-arrow"></i>
          </div>
          <h3>Salary Insights</h3>
          <p>Interactive charts and analytics to visualize compensation trends, growth, and department comparisons.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card" style="background:#fff;border:1px solid #e2e8f0;color:#0f172a;">
          <div class="feature-icon" style="color:#d97706;">
            <i class="bi bi-people-fill"></i>
          </div>
          <h3>Employee Portal</h3>
          <p>Empower employees with self-service access to salary breakdowns, payroll history, and feedback tools.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer style="background:#0f172a;color:#94a3b8;padding:32px 20px;text-align:center;">
  <div class="container">
    <p style="margin:0;font-size:14px;">
      &copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.
    </p>
    <p style="margin:8px 0 0;font-size:12px;">
      Built with <i class="bi bi-heart-fill text-danger"></i> for modern HR teams
    </p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
