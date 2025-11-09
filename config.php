<?php
/**
 * config.php — Application configuration
 * Purpose: Secure session settings, security headers, and constants
 * Features: Session timeout, cookie security, XSS protection
 */

// Load environment variables from .env file
$env_file = __DIR__ . '/.env';
if (!file_exists($env_file)) {
    die("Critical Error: .env file not found. Please create it with your database credentials.");
}

$env = parse_ini_file($env_file);

// ============================================
// SESSION SECURITY CONFIGURATION
// ============================================

// Prevent session hijacking - only use cookies for sessions
ini_set('session.use_only_cookies', 1);

// Prevent JavaScript access to session cookie (XSS protection)
ini_set('session.cookie_httponly', 1);

// Only send cookie over HTTPS (set to 1 in production)
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);

// Prevent CSRF attacks
ini_set('session.cookie_samesite', 'Strict');

// Session lifetime configuration
$session_lifetime = $env['SESSION_LIFETIME'] ?? 3600; // Default 1 hour
ini_set('session.gc_maxlifetime', $session_lifetime);

// Session cookie lifetime
$cookie_lifetime = $env['COOKIE_LIFETIME'] ?? 604800; // Default 7 days
session_set_cookie_params([
    'lifetime' => $cookie_lifetime,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? '',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Strict'
]);

// ============================================
// SECURITY HEADERS
// ============================================

// Prevent MIME type sniffing
header("X-Content-Type-Options: nosniff");

// Prevent clickjacking attacks
header("X-Frame-Options: DENY");

// Enable XSS filter in browsers
header("X-XSS-Protection: 1; mode=block");

// Referrer policy
header("Referrer-Policy: strict-origin-when-cross-origin");

// ============================================
// APPLICATION CONSTANTS
// ============================================

define('APP_NAME', $env['APP_NAME'] ?? 'BlogWithMe');
define('APP_URL', $env['APP_URL'] ?? 'http://localhost');
define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
define('DB_USER', $env['DB_USER'] ?? 'root');
define('DB_PASS', $env['DB_PASS'] ?? '');
define('DB_NAME', $env['DB_NAME'] ?? 'blogdb');
define('DB_PORT', $env['DB_PORT'] ?? 3306);

// Error reporting (disable in production)
if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
    ini_set('display_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}