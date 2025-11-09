<?php
/**
 * includes/functions.php
 * Utility functions for the blog application
 * 
 * Contains helper functions for:
 * - Error logging
 * - Input sanitization
 * - Date formatting
 * - Text manipulation
 * - Validation
 */

/**
 * Log errors to a file
 * Creates errors directory if it doesn't exist
 * 
 * @param string $message Error message to log
 * @return void
 */
function log_error($message) {
    $log_dir = __DIR__ . '/../errors';
    
    // Create errors directory if it doesn't exist
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/error_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] {$message}" . PHP_EOL;
    
    // Write to log file
    error_log($log_message, 3, $log_file);
}

/**
 * Sanitize output for HTML display
 * Prevents XSS attacks
 * 
 * @param string $text Text to sanitize
 * @return string Sanitized text
 */
function clean_output($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize input data
 * Removes extra whitespace and special characters
 * 
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Truncate text to specified length
 * Adds ellipsis if text is longer than limit
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to add (default: '...')
 * @return string Truncated text
 */
function truncate_text($text, $length = 150, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Format date for display
 * 
 * @param string $date Date string
 * @param string $format Date format (default: 'F j, Y')
 * @return string Formatted date
 */
function format_date($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 * 
 * @param string $datetime Datetime string
 * @return string Formatted datetime
 */
function format_datetime($datetime) {
    return date('F j, Y \a\t g:i A', strtotime($datetime));
}

/**
 * Time ago format (e.g., "2 hours ago")
 * 
 * @param string $datetime Datetime string
 * @return string Time ago string
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return "just now";
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . " minute" . ($mins > 1 ? "s" : "") . " ago";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    } else {
        return format_date($datetime);
    }
}

/**
 * Check if email is valid
 * 
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if URL is valid
 * 
 * @param string $url URL to validate
 * @return bool True if valid, false otherwise
 */
function is_valid_url($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Generate a secure random token
 * 
 * @param int $length Token length in bytes (will be doubled in hex)
 * @return string Random token
 */
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Get client IP address
 * 
 * @return string IP address
 */
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }
}

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if request is POST
 * 
 * @return bool True if POST request
 */
function is_post_request() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Check if request is GET
 * 
 * @return bool True if GET request
 */
function is_get_request() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Get base URL of the application
 * 
 * @return string Base URL
 */
function get_base_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    return "{$protocol}://{$host}{$path}";
}

/**
 * Display success message from session
 * 
 * @return string|null Success message HTML or null
 */
function show_success_message() {
    if (isset($_SESSION['success_message'])) {
        $message = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
        return '' . htmlspecialchars($message) . '';
    }
    return null;
}

/**
 * Display error message from session
 * 
 * @return string|null Error message HTML or null
 */
function show_error_message() {
    if (isset($_SESSION['error_message'])) {
        $message = $_SESSION['error_message'];
        unset($_SESSION['error_message']);
        return '' . htmlspecialchars($message) . '';
    }
    return null;
}

/**
 * Count words in text
 * 
 * @param string $text Text to count words in
 * @return int Word count
 */
function word_count($text) {
    return str_word_count(strip_tags($text));
}

/**
 * Estimate reading time in minutes
 * 
 * @param string $text Text content
 * @param int $wpm Words per minute (default: 200)
 * @return int Reading time in minutes
 */
function reading_time($text, $wpm = 200) {
    $words = word_count($text);
    $minutes = ceil($words / $wpm);
    return max(1, $minutes);
}

/**
 * Create slug from title (URL-friendly string)
 * 
 * @param string $title Title to convert
 * @return string URL-friendly slug
 */
function create_slug($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}