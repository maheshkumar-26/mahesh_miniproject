<?php
/**
 * Authentication Helper
 */

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if current user is admin
 */
function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
}

/**
 * Check if current user is employee
 */
function isEmployee(): bool {
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'employee';
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        setFlash('warning', 'Please login to continue.');
        redirect(BASE_URL . 'auth/login.php');
    }
}

/**
 * Require admin role
 */
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        setFlash('danger', 'Access denied. Admin only.');
        redirect(BASE_URL . 'employee/dashboard.php');
    }
}

/**
 * Require employee role
 */
function requireEmployee(): void {
    requireLogin();
    if (!isEmployee()) {
        setFlash('danger', 'Access denied.');
        redirect(BASE_URL . 'admin/dashboard.php');
    }
}

/**
 * Login user - set session
 */
function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'];
}

/**
 * Logout user
 */
function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

/**
 * Get current logged-in user data
 */
function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return db()->fetchOne('SELECT * FROM users WHERE id=?', 'i', [$_SESSION['user_id']]);
}

/**
 * Get employee record for current user
 */
function currentEmployee(): ?array {
    if (!isEmployee()) return null;
    return db()->fetchOne(
        'SELECT e.*, d.name as department_name FROM employees e
         JOIN departments d ON e.department_id = d.id
         WHERE e.user_id=?',
        'i', [$_SESSION['user_id']]
    );
}
