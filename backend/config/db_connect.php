<?php
/**
 * Database connection for Student Wellness Tracking Dashboard
 * Include this file in every page that needs database access.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_wellness_db');

$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    // Don't die immediately - let the calling page handle the error
    $conn = null;
}
