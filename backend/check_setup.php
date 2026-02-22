<?php
/**
 * Setup Verification Script
 * Run this to check if your environment is ready
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Student Wellness Dashboard - Setup Check</h2>";
echo "<hr>";

// Check PHP version
echo "<h3>1. PHP Version</h3>";
echo "PHP Version: " . phpversion() . "<br>";
if (version_compare(phpversion(), '7.0', '>=')) {
    echo "<span style='color:green;'>✓ PHP version is OK</span><br>";
} else {
    echo "<span style='color:red;'>✗ PHP 7.0+ required</span><br>";
}

// Check MySQL extension
echo "<h3>2. MySQL Extension</h3>";
if (extension_loaded('mysqli')) {
    echo "<span style='color:green;'>✓ MySQLi extension loaded</span><br>";
} else {
    echo "<span style='color:red;'>✗ MySQLi extension not found</span><br>";
}

// Check database connection
echo "<h3>3. Database Connection</h3>";
require_once 'config/db_connect.php';
if ($conn) {
    echo "<span style='color:green;'>✓ Database connection successful</span><br>";
    
    // Check if database exists
    $result = mysqli_query($conn, "SHOW DATABASES LIKE 'student_wellness_db'");
    if (mysqli_num_rows($result) > 0) {
        echo "<span style='color:green;'>✓ Database 'student_wellness_db' exists</span><br>";
        
        // Check tables
        mysqli_select_db($conn, 'student_wellness_db');
        $tables = ['users', 'wellness_data'];
        foreach ($tables as $table) {
            $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
            if (mysqli_num_rows($result) > 0) {
                echo "<span style='color:green;'>✓ Table '$table' exists</span><br>";
            } else {
                echo "<span style='color:red;'>✗ Table '$table' not found. Run sql/database.sql</span><br>";
            }
        }
        
        // Check users
        $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM users");
        $row = mysqli_fetch_assoc($result);
        if ($row['cnt'] > 0) {
            echo "<span style='color:green;'>✓ Users table has {$row['cnt']} user(s)</span><br>";
        } else {
            echo "<span style='color:orange;'>⚠ No users found. Run setup_passwords.php</span><br>";
        }
    } else {
        echo "<span style='color:red;'>✗ Database 'student_wellness_db' not found. Import sql/database.sql</span><br>";
    }
} else {
    echo "<span style='color:red;'>✗ Database connection failed: " . mysqli_connect_error() . "</span><br>";
}

// Check file structure (using current layout: backend + frontend)
echo "<h3>4. File Structure</h3>";
$required_files = [
    // Backend (auth + config)
    'login.php',
    'register.php',
    'config/db_connect.php',
    // Frontend (dashboards + assets)
    '../frontend/index.php',
    '../frontend/dashboard.php',
    '../frontend/admin_dashboard.php',
    '../frontend/check_in.php',
    '../frontend/view_wellness.php',
    '../frontend/reports.php',
    '../frontend/assets/css/style.css',
    '../frontend/assets/js/charts.js',
    '../frontend/includes/header.php',
    '../frontend/includes/footer.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<span style='color:green;'>✓ $file exists</span><br>";
    } else {
        echo "<span style='color:red;'>✗ $file missing</span><br>";
    }
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>If database is missing, import <strong>sql/database.sql</strong> in phpMyAdmin</li>";
echo "<li>If users are missing, visit <strong>setup_passwords.php</strong> in browser</li>";
echo "<li>Then go to <strong>login.php</strong> and login with admin/admin123 or student/student123</li>";
echo "</ol>";
echo "<p><a href='login.php'>Go to Login Page</a> | <a href='setup_passwords.php'>Setup Passwords</a></p>";
?>
