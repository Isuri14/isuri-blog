<?php
/**
 * config.php — Application configuration
 * Purpose: Secure session settings, security headers, and constants
 * Features: Session timeout, cookie security, XSS protection
 */

// ============================================
// ENVIRONMENT LOADING (supports missing .env)
// ============================================

$env_file = __DIR__ . '/.env';
$env = [];

if (file_exists($env_file)) {
    $env = parse_ini_file($env_file);
} else {
    // Log a warning instead of failing
    $error_log_path = __DIR__ . '/errors/error_log.txt';
    if (!is_dir(dirname($error_log_path))) {
        mkdir(dirname($error_log_path), 0775, true);
    }
    error_log("[" . date('Y-m-d H:i:s') . "] Warning: .env file not found. Using defaults or system environment variables." . PHP_EOL, 3, $error_log_path);
}

// Helper function to load environment variable with fallback
function env_or_default($key, $default = null) {
    global $env;
    return $env[$key] ?? getenv($key) ?: $default;
}

// ============================================
// SESSION SECURITY CONFIGURATION
// ============================================

// Prevent session hijacking - only use cookies for sessions
ini_set('session.use_only_cookies', 1);

// Prevent JavaScript access to session cookie (XSS protection)
ini_set('session.cookie_httponly', 1);

// Only send cookie over HTTPS (set to 1 in production)
$secure_cookie = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0;
ini_set('session.cookie_secure', $secure_cookie);

// Prevent CSRF attacks
ini_set('session.cookie_samesite', 'Strict');

// Session lifetime configuration
$session_lifetime = env_or_default('SESSION_LIFETIME', 3600); // Default 1 hour
ini_set('session.gc_maxlifetime', $session_lifetime);

// Session cookie lifetime
$cookie_lifetime = env_or_default('COOKIE_LIFETIME', 604800); // Default 7 days
session_set_cookie_params([
    'lifetime' => $cookie_lifetime,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? '',
    'secure' => $secure_cookie,
    'httponly' => true,
    'samesite' => 'Strict'
]);

// ============================================
// SECURITY HEADERS
// ============================================

header("X-Content-Type-Options: nosniff");            // Prevent MIME sniffing
header("X-Frame-Options: DENY");                      // Prevent clickjacking
header("X-XSS-Protection: 1; mode=block");            // Enable XSS protection
header("Referrer-Policy: strict-origin-when-cross-origin"); // Limit referrer leakage

// ============================================
// APPLICATION CONSTANTS
// ============================================

# Database configuration for local development
define('APP_NAME', env_or_default('APP_NAME', 'BlogWithMe'));
define('APP_URL', env_or_default('APP_URL', 'http://localhost'));
define('DB_HOST', env_or_default('DB_HOST', 'sql12.freesqldatabase.com'));
define('DB_USER', env_or_default('DB_USER', 'sql12806907'));
define('DB_PASS', env_or_default('DB_PASS', '1766xZdFfd'));
define('DB_NAME', env_or_default('DB_NAME', 'sql12806907'));
define('DB_PORT', env_or_default('DB_PORT', 3306));
define('ENVIRONMENT', env_or_default('ENVIRONMENT', 'development'));

// ============================================
// ERROR REPORTING
// ============================================

if (ENVIRONMENT === 'production') {
    ini_set('display_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
