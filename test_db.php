<?php
require_once 'includes/db.php';

try {
    $result = $conn->query("SELECT 1");
    if ($result) {
        echo "Database connection OK!";
    } else {
        echo "Query failed: " . $conn->error;
    }
} catch (mysqli_sql_exception $e) {
    echo "Database error: " . $e->getMessage();
}
?>
