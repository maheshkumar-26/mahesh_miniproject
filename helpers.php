<?php
/**
 * Global Helper Functions
 */

/**
 * Sanitize input
 */
function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect helper
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * Flash message setter
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Flash message getter & clear
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Format currency
 */
function formatCurrency(float $amount): string {
    return APP_CURRENCY . number_format($amount, 2);
}

/**
 * Format month name
 */
function monthName(int $month): string {
    return date('F', mktime(0, 0, 0, $month, 1));
}

/**
 * Generate CSRF token
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * CSRF hidden input field
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

/**
 * Upload profile image
 */
function uploadImage(array $file, string $oldImage = 'default.png'): string {
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        return $oldImage;
    }
    if ($file['size'] > 2 * 1024 * 1024) { // 2MB
        return $oldImage;
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('emp_', true) . '.' . $ext;
    $dest = UPLOAD_PATH . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        // Delete old image if not default
        if ($oldImage !== 'default.png' && file_exists(UPLOAD_PATH . $oldImage)) {
            unlink(UPLOAD_PATH . $oldImage);
        }
        return $filename;
    }
    return $oldImage;
}

/**
 * Get profile image URL
 */
function profileImageUrl(string $filename): string {
    if ($filename === 'default.png' || !file_exists(UPLOAD_PATH . $filename)) {
        return BASE_URL . 'assets/images/default-avatar.svg';
    }
    return UPLOAD_URL . $filename;
}

/**
 * Pagination helper
 */
function paginate(int $total, int $perPage, int $currentPage, string $url): array {
    $totalPages = (int)ceil($total / $perPage);
    return [
        'total'        => $total,
        'per_page'     => $perPage,
        'current_page' => $currentPage,
        'total_pages'  => $totalPages,
        'offset'       => ($currentPage - 1) * $perPage,
        'url'          => $url,
    ];
}

/**
 * Add notification
 */
function addNotification(int $userId, string $title, string $message, string $type = 'info'): void {
    db()->execute(
        'INSERT INTO notifications (user_id, title, message, type) VALUES (?,?,?,?)',
        'isss',
        [$userId, $title, $message, $type]
    );
}

/**
 * Get unread notification count
 */
function unreadNotifCount(int $userId): int {
    $row = db()->fetchOne(
        'SELECT COUNT(*) as cnt FROM notifications WHERE user_id=? AND is_read=0',
        'i', [$userId]
    );
    return (int)($row['cnt'] ?? 0);
}

/**
 * Generate employee code
 */
function generateEmployeeCode(): string {
    $row = db()->fetchOne('SELECT MAX(id) as max_id FROM employees');
    $next = (int)($row['max_id'] ?? 0) + 1;
    return 'ECI-' . str_pad($next, 3, '0', STR_PAD_LEFT);
}

/**
 * Active nav link helper
 */
function isActive(string $page): string {
    $current = basename($_SERVER['PHP_SELF']);
    return ($current === $page) ? 'active' : '';
}
