<?php
/**
 * Entry point - redirect to login or role-specific dashboard
 */
session_start();

if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

header('Location: ../backend/login.php');
exit;
