<?php
require_once __DIR__ . '/../config/config.php';
logoutUser();
setFlash('success', 'You have been logged out successfully.');
redirect(BASE_URL . 'auth/login.php');
