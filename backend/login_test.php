<?php
/**
 * Diagnostic version of login.php - shows errors clearly
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Step 1: PHP is working</h2>";

session_start();
echo "<p>✓ Session started</p>";

if (isset($_SESSION['user_id'])) {
    echo "<p>You are already logged in. <a href='index.php'>Go to dashboard</a></p>";
    exit;
}

echo "<h2>Step 2: Checking database connection</h2>";

if (file_exists('config/db_connect.php')) {
    echo "<p>✓ config/db_connect.php exists</p>";
    require_once 'config/db_connect.php';
    
    if ($conn) {
        echo "<p>✓ Database connected successfully</p>";
    } else {
        echo "<p style='color:red;'>✗ Database connection failed</p>";
        echo "<p>Make sure MySQL is running and database 'student_wellness_db' exists.</p>";
    }
} else {
    echo "<p style='color:red;'>✗ config/db_connect.php NOT FOUND</p>";
    echo "<p>Current directory: " . __DIR__ . "</p>";
}

echo "<h2>Step 3: Checking CSS file</h2>";
if (file_exists('../frontend/assets/css/style.css')) {
    echo "<p>✓ ../frontend/assets/css/style.css exists</p>";
} else {
    echo "<p style='color:red;'>✗ assets/css/style.css NOT FOUND</p>";
}

echo "<hr>";
echo "<h2>If all checks passed above, try the real login:</h2>";
echo "<p><a href='login.php'>Go to Login Page</a></p>";
?>
