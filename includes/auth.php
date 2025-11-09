<?php
/**
 * includes/auth.php
 * Enhanced authentication with session timeout
 */

require_once __DIR__ . '/../config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check session timeout
if (isset($_SESSION['LAST_ACTIVITY'])) {
    $session_lifetime = ini_get('session.gc_maxlifetime');
    if (time() - $_SESSION['LAST_ACTIVITY'] > $session_lifetime) {
        // Session expired
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=1");
        exit();
    }
}
$_SESSION['LAST_ACTIVITY'] = time();

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Require login - redirect to login if not authenticated
 */
function require_login() {
    if (!is_logged_in()) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['PHP_SELF']);
        header("Location: {$protocol}://{$host}{$path}/login.php");
        exit;
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
    return $_SESSION['username'] ?? null;
}

/**
 * Set remember me cookie
 */
function set_remember_me_cookie($user_id) {
    $token = bin2hex(random_bytes(32));
    $expiry = time() + (30 * 24 * 60 * 60); // 30 days
    
    setcookie('remember_token', $token, [
        'expires' => $expiry,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    // Optionally, store token in database for verification
    // Example:
    // $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    // $stmt = $conn->prepare("UPDATE user SET remember_token = ? WHERE id = ?");
    // $stmt->bind_param("si", $token, $user_id);
    // $stmt->execute();
    // $stmt->close();
}
