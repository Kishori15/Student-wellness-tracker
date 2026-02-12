<?php
/**
 * Simple test to check if register.php can load
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Register.php Test</h2>";
echo "<p>✓ PHP is working</p>";

if (file_exists('register.php')) {
    echo "<p>✓ register.php file exists</p>";
} else {
    echo "<p style='color:red;'>✗ register.php NOT FOUND</p>";
}

if (file_exists('config/db_connect.php')) {
    echo "<p>✓ config/db_connect.php exists</p>";
    require_once 'config/db_connect.php';
    if ($conn) {
        echo "<p>✓ Database connected</p>";
    } else {
        echo "<p style='color:orange;'>⚠ Database connection failed (but page should still load)</p>";
    }
} else {
    echo "<p style='color:red;'>✗ config/db_connect.php NOT FOUND</p>";
}

if (file_exists('../frontend/assets/css/style.css')) {
    echo "<p>✓ CSS file exists</p>";
} else {
    echo "<p style='color:orange;'>⚠ CSS file missing (page will work but look bad)</p>";
}

echo "<hr>";
echo "<h3>Try the actual register page:</h3>";
echo "<p><a href='register.php'>Open register.php</a></p>";
?>
