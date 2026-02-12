<?php
/**
 * One-time setup script to set bcrypt passwords for default users
 * Run this once after importing database.sql, then delete this file
 */
require_once 'config/db_connect.php';

$users = [
    ['username' => 'admin', 'password' => 'admin123', 'role' => 'admin'],
    ['username' => 'student', 'password' => 'student123', 'role' => 'student']
];

foreach ($users as $u) {
    $hash = password_hash($u['password'], PASSWORD_DEFAULT);
    
    // Check if user exists
    $check = mysqli_prepare($conn, 'SELECT id FROM users WHERE username = ?');
    mysqli_stmt_bind_param($check, 's', $u['username']);
    mysqli_stmt_execute($check);
    $exists = mysqli_stmt_get_result($check)->num_rows > 0;
    mysqli_stmt_close($check);
    
    if ($exists) {
        // Update existing user
        $stmt = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE username = ?');
        mysqli_stmt_bind_param($stmt, 'ss', $hash, $u['username']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "Updated password for {$u['username']}<br>";
    } else {
        // Insert new user
        $stmt = mysqli_prepare($conn, 'INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sss', $u['username'], $hash, $u['role']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "Created user {$u['username']}<br>";
    }
}

echo "<br>Setup complete! Please delete this file (setup_passwords.php) for security.<br>";
echo "<a href='login.php'>Go to Login</a>";
