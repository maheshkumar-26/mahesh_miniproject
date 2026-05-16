<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><i class="bi bi-bar-chart-fill"></i></div>
    <div class="brand-text">
      <span class="brand-name">ECI</span>
      <span class="brand-sub">Admin Panel</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="<?= BASE_URL ?>admin/dashboard.php" class="nav-item <?= isActive('dashboard.php') ?>">
      <i class="bi bi-speedometer2"></i><span>Dashboard</span>
    </a>

    <div class="nav-section-label">Employees</div>
    <a href="<?= BASE_URL ?>admin/employees.php" class="nav-item <?= isActive('employees.php') ?>">
      <i class="bi bi-people-fill"></i><span>Employees</span>
    </a>
    <a href="<?= BASE_URL ?>admin/add_employee.php" class="nav-item <?= isActive('add_employee.php') ?>">
      <i class="bi bi-person-plus-fill"></i><span>Add Employee</span>
    </a>
    <a href="<?= BASE_URL ?>admin/departments.php" class="nav-item <?= isActive('departments.php') ?>">
      <i class="bi bi-building"></i><span>Departments</span>
    </a>

    <div class="nav-section-label">Payroll</div>
    <a href="<?= BASE_URL ?>admin/payroll.php" class="nav-item <?= isActive('payroll.php') ?>">
      <i class="bi bi-cash-stack"></i><span>Payroll</span>
    </a>
    <a href="<?= BASE_URL ?>admin/add_payroll.php" class="nav-item <?= isActive('add_payroll.php') ?>">
      <i class="bi bi-plus-circle-fill"></i><span>Generate Payroll</span>
    </a>

    <div class="nav-section-label">Reports</div>
    <a href="<?= BASE_URL ?>admin/reports.php" class="nav-item <?= isActive('reports.php') ?>">
      <i class="bi bi-file-earmark-bar-graph-fill"></i><span>Reports</span>
    </a>

    <div class="nav-section-label">Account</div>
    <a href="<?= BASE_URL ?>admin/profile.php" class="nav-item <?= isActive('profile.php') ?>">
      <i class="bi bi-person-circle"></i><span>Profile</span>
    </a>
    <a href="<?= BASE_URL ?>auth/logout.php" class="nav-item text-danger">
      <i class="bi bi-box-arrow-right"></i><span>Logout</span>
    </a>
  </nav>
</aside>
