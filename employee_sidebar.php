<?php $emp = currentEmployee(); ?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><i class="bi bi-bar-chart-fill"></i></div>
    <div class="brand-text">
      <span class="brand-name">ECI</span>
      <span class="brand-sub">My Portal</span>
    </div>
  </div>

  <div class="sidebar-user">
    <img src="<?= profileImageUrl($emp['profile_image'] ?? 'default.png') ?>" alt="Avatar" class="sidebar-avatar">
    <div>
      <div class="sidebar-user-name"><?= sanitize($_SESSION['user_name']) ?></div>
      <div class="sidebar-user-role"><?= sanitize($emp['designation'] ?? 'Employee') ?></div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="<?= BASE_URL ?>employee/dashboard.php" class="nav-item <?= isActive('dashboard.php') ?>">
      <i class="bi bi-speedometer2"></i><span>Dashboard</span>
    </a>

    <div class="nav-section-label">Compensation</div>
    <a href="<?= BASE_URL ?>employee/salary_breakdown.php" class="nav-item <?= isActive('salary_breakdown.php') ?>">
      <i class="bi bi-pie-chart-fill"></i><span>Salary Breakdown</span>
    </a>
    <a href="<?= BASE_URL ?>employee/payroll_history.php" class="nav-item <?= isActive('payroll_history.php') ?>">
      <i class="bi bi-clock-history"></i><span>Payroll History</span>
    </a>
    <a href="<?= BASE_URL ?>employee/insights.php" class="nav-item <?= isActive('insights.php') ?>">
      <i class="bi bi-graph-up-arrow"></i><span>Compensation Insights</span>
    </a>
    <a href="<?= BASE_URL ?>employee/feedback.php" class="nav-item <?= isActive('feedback.php') ?>">
      <i class="bi bi-star-fill"></i><span>Satisfaction Index</span>
    </a>

    <div class="nav-section-label">Account</div>
    <a href="<?= BASE_URL ?>employee/profile.php" class="nav-item <?= isActive('profile.php') ?>">
      <i class="bi bi-person-circle"></i><span>My Profile</span>
    </a>
    <a href="<?= BASE_URL ?>auth/logout.php" class="nav-item text-danger">
      <i class="bi bi-box-arrow-right"></i><span>Logout</span>
    </a>
  </nav>
</aside>
