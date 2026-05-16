<?php
$notifCount = isLoggedIn() ? unreadNotifCount((int)$_SESSION['user_id']) : 0;
$notifications = isLoggedIn() ? db()->fetchAll(
    'SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 5',
    'i', [$_SESSION['user_id']]
) : [];
?>
<header class="topbar">
  <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
    <i class="bi bi-list"></i>
  </button>

  <div class="topbar-title"><?= isset($pageTitle) ? sanitize($pageTitle) : APP_NAME ?></div>

  <div class="topbar-actions">
    <!-- Notifications -->
    <div class="dropdown">
      <button class="topbar-btn position-relative" data-bs-toggle="dropdown" aria-label="Notifications">
        <i class="bi bi-bell-fill"></i>
        <?php if ($notifCount > 0): ?>
          <span class="badge-dot"><?= $notifCount ?></span>
        <?php endif; ?>
      </button>
      <div class="dropdown-menu dropdown-menu-end notif-dropdown">
        <div class="notif-header">
          <span>Notifications</span>
          <?php if ($notifCount > 0): ?>
            <a href="<?= BASE_URL ?><?= isAdmin() ? 'admin' : 'employee' ?>/notifications.php" class="mark-all-read">Mark all read</a>
          <?php endif; ?>
        </div>
        <?php if (empty($notifications)): ?>
          <div class="notif-empty"><i class="bi bi-bell-slash"></i> No notifications</div>
        <?php else: ?>
          <?php foreach ($notifications as $n): ?>
            <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
              <div class="notif-icon notif-<?= $n['type'] ?>">
                <i class="bi bi-<?= $n['type'] === 'success' ? 'check-circle' : ($n['type'] === 'warning' ? 'exclamation-triangle' : 'info-circle') ?>-fill"></i>
              </div>
              <div class="notif-body">
                <div class="notif-title"><?= sanitize($n['title']) ?></div>
                <div class="notif-msg"><?= sanitize($n['message']) ?></div>
                <div class="notif-time"><?= date('d M, h:i A', strtotime($n['created_at'])) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- User Menu -->
    <div class="dropdown">
      <button class="topbar-user" data-bs-toggle="dropdown">
        <img src="<?= isEmployee() ? profileImageUrl(currentEmployee()['profile_image'] ?? 'default.png') : BASE_URL . 'assets/images/default-avatar.svg' ?>" alt="User" class="topbar-avatar">
        <span class="d-none d-md-inline"><?= sanitize($_SESSION['user_name'] ?? 'User') ?></span>
        <i class="bi bi-chevron-down small"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="<?= BASE_URL ?><?= isAdmin() ? 'admin' : 'employee' ?>/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
      </ul>
    </div>
  </div>
</header>
