<?php
/**
 * includes/header.php
 * Page header with navigation
 * 
 * This file includes the HTML head and opening body tag
 * Load this at the beginning of each page
 */

// Get base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$base_url = "{$protocol}://{$host}{$path}";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BlogWithMe - Share your thoughts with the world">
    <meta name="author" content="K.H.I. Hansani">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' | BlogWithMe' : 'BlogWithMe' ?></title>
    
    <!-- Favicon (optional) -->
    <link rel="icon" href="<?= $base_url ?>/assets/images/favicon.ico" type="image/x-icon">
    
    <!-- CSS -->