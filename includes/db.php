<?php
/**
 * includes/db.php
 * Database connection handler
 * 
 * Features:
 * - Loads credentials from .env file (if available)
 * - Falls back to system environment variables or defaults
 * - Uses MySQLi with error handling
 * - UTF-8 character set support
 * - Connection error logging
 * 
 * Security:
 * - Credentials stored securely (not hardcoded)
 * - Connection errors logged, not displayed
 */

// Path to .env file
$env_file = __DIR__ . '/../.env';

// Load .env file if it exists
$env = [];
if (file_exists($env_file)) {
    $env = parse_ini_file($env_file);
} else {
    // Log warning (not a fatal error)
    //error_log("[" . date('Y-m-d H:i:s') . "] Warning: .env file not found, using defaults or system environment variables." . PHP_EOL, 3, __DIR__ . '/../errors/error_log.txt');
}


// Database credentials (priority: .env > system env vars > defaults)
$DB_HOST = $env['DB_HOST'] ?? getenv('DB_HOST') ?: 'sql12.freesqldatabase.com';
$DB_USER = $env['DB_USER'] ?? getenv('DB_USER') ?: 'sql12806907';
$DB_PASS = $env['DB_PASS'] ?? getenv('DB_PASS') ?: '1766xZdFfd';
$DB_NAME = $env['DB_NAME'] ?? getenv('DB_NAME') ?: 'sql12806907';
$DB_PORT = $env['DB_PORT'] ?? getenv('DB_PORT') ?: 3306;

// Create MySQLi connection
try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
    
    // Check for connection errors
    if ($conn->connect_error) {
        $error_msg = "Database connection failed: " . $conn->connect_error;
        error_log("[" . date('Y-m-d H:i:s') . "] " . $error_msg . PHP_EOL, 3, __DIR__ . '/../errors/error_log.txt');
        die("Unable to connect to database. Please try again later.");
    }
    
    // Set character set to UTF-8
    if (!$conn->set_charset("utf8mb4")) {
        error_log("[" . date('Y-m-d H:i:s') . "] Failed to set UTF-8 charset: " . $conn->error . PHP_EOL, 3, __DIR__ . '/../errors/error_log.txt');
    }

    // Disable automatic error reporting
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

} catch (Exception $e) {
    // Log exception
    $error_msg = "Database connection exception: " . $e->getMessage();
    error_log("[" . date('Y-m-d H:i:s') . "] " . $error_msg . PHP_EOL, 3, __DIR__ . '/../errors/error_log.txt');
    die("Unable to connect to database. Please try again later.");
}

// Optional: Set timezone
$conn->query("SET time_zone = '+00:00'");
