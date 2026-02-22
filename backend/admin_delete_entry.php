<?php
/**
 * Admin: Delete Individual Wellness Entry
 * RBAC: Admin only, uses prepared statements, prevents SQL injection
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'config/db_connect.php';

$entry_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$message = '';
$error = '';

if ($entry_id <= 0) {
    header('Location: ../frontend/admin_dashboard.php');
    exit;
}

// Verify entry exists and get student_id for redirect
$stmt = mysqli_prepare($conn, 'SELECT user_id, entry_date FROM wellness_data WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $entry_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$entry = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$entry) {
    $error = 'Wellness entry not found.';
} else {
    // Use student_id from entry if not provided in URL
    if ($student_id <= 0) {
        $student_id = (int)$entry['user_id'];
    }
    
    // Verify the student_id matches (security check)
    if ($student_id !== (int)$entry['user_id']) {
        $error = 'Invalid student ID.';
    } else {
        // Delete the wellness entry using prepared statement
        $stmt = mysqli_prepare($conn, 'DELETE FROM wellness_data WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $entry_id);
        mysqli_stmt_execute($stmt);
        $deleted = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if ($deleted > 0) {
            $_SESSION['admin_message'] = 'Wellness entry from ' . htmlspecialchars($entry['entry_date']) . ' deleted successfully.';
        } else {
            $error = 'Failed to delete wellness entry.';
        }
    }
}

if ($error) {
    $_SESSION['admin_error'] = $error;
}

// Redirect back to student view if student_id provided, otherwise dashboard
if ($student_id > 0) {
    header('Location: ../frontend/admin_view_student.php?id=' . $student_id);
} else {
    header('Location: ../frontend/admin_dashboard.php');
}
exit;
