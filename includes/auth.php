<?php
/**
 * includes/auth.php
 * Enhanced authentication with session timeout
 */

// Load config BEFORE starting session (critical!)
require_once __DIR__ . '/../config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check session timeout
if (isset($_SESSION['LAST_ACTIVITY'])) {
    $session_lifetime = 3600; // 1 hour
    if (time() - $_SESSION['LAST_ACTIVITY'] > $session_lifetime) {
        // Session expired
        session_unset();
        session_destroy();
        session_start(); // Start new session
        $_SESSION['error_message'] = "Your session has expired. Please login again.";
        header("Location: login.php");
        exit();
    }
}
$_SESSION['LAST_ACTIVITY'] = time();

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require login - redirect to login if not authenticated
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['error_message'] = "Please login to access this page.";
        header("Location: login.php");
        exit();
    }
}

/**
 * Get current user ID
 */
function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 */
function current_username() {
    return $_SESSION['username'] ?? 'Guest';
}

/**
 * Logout user
 */
function logout_user() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
