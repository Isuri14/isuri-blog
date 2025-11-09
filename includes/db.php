<?php
/**
 * includes/db.php
 * Database connection handler
 * 
 * Features:
 * - Loads credentials from .env file
 * - Uses MySQLi with error handling
 * - UTF-8 character set support
 * - Connection error logging
 * 
 * Security:
 * - Credentials stored in .env (not in code)
 * - Connection errors logged, not displayed
 */

// Load environment variables
$env_file = __DIR__ . '/../.env';

if (!file_exists($env_file)) {
    die("Critical Error: .env file not found. Please create the .env file with your database credentials.");
}

$env = parse_ini_file($env_file);

// Database credentials from .env
$DB_HOST = $env['DB_HOST'] ?? 'localhost';
$DB_USER = $env['DB_USER'] ?? 'root';
$DB_PASS = $env['DB_PASS'] ?? '';
$DB_NAME = $env['DB_NAME'] ?? 'blogdb';
$DB_PORT = $env['DB_PORT'] ?? 3306;

// Create MySQLi connection
try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
    
    // Check for connection errors
    if ($conn->connect_error) {
        // Log error
        $error_msg = "Database connection failed: " . $conn->connect_error;
        error_log("[" . date('Y-m-d H:i:s') . "] " . $error_msg . PHP_EOL, 3, __DIR__ . '/../errors/error_log.txt');
        
        // Show user-friendly message
        die("Unable to connect to database. Please try again later.");
    }
    
    // Set character set to UTF-8 for proper encoding
    if (!$conn->set_charset("utf8mb4")) {
        error_log("[" . date('Y-m-d H:i:s') . "] Failed to set UTF-8 charset: " . $conn->error . PHP_EOL, 3, __DIR__ . '/../errors/error_log.txt');
    }
    
    // Disable automatic error reporting (we'll handle errors manually)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    
} catch (Exception $e) {
    // Log exception
    $error_msg = "Database connection exception: " . $e->getMessage();
    error_log("[" . date('Y-m-d H:i:s') . "] " . $error_msg . PHP_EOL, 3, __DIR__ . '/../errors/error_log.txt');
    
    // Show user-friendly message
    die("Unable to connect to database. Please try again later.");
}

// Optional: Set timezone
$conn->query("SET time_zone = '+00:00'");