<?php
/**
 * Application Configuration
 */

// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// App constants
define('APP_NAME', 'Employee Compensation Insights');
define('APP_VERSION', '1.0.0');
define('APP_CURRENCY', '₹');

// Include DB
require_once __DIR__ . '/db.php';

// Include helpers
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_helper.php';
