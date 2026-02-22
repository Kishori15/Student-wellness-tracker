<?php
/**
 * Admin: Delete Student Account and All Wellness Data
 * RBAC: Admin only, uses prepared statements, prevents SQL injection
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'config/db_connect.php';

$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

if ($student_id <= 0) {
    header('Location: ../frontend/admin_dashboard.php');
    exit;
}

// Verify student exists and is actually a student (security check)
$stmt = mysqli_prepare($conn, 'SELECT id, username FROM users WHERE id = ? AND role = "student"');
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$student) {
    $error = 'Student not found or invalid.';
} else {
    // Begin transaction for atomicity
    mysqli_begin_transaction($conn);
    
    try {
        // Delete all wellness data for this student (CASCADE should handle this, but explicit is safer)
        $stmt = mysqli_prepare($conn, 'DELETE FROM wellness_data WHERE user_id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $student_id);
        mysqli_stmt_execute($stmt);
        $deleted_entries = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        // Delete the student account
        $stmt = mysqli_prepare($conn, 'DELETE FROM users WHERE id = ? AND role = "student"');
        mysqli_stmt_bind_param($stmt, 'i', $student_id);
        mysqli_stmt_execute($stmt);
        $deleted_user = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if ($deleted_user > 0) {
            mysqli_commit($conn);
            $_SESSION['admin_message'] = 'Student "' . htmlspecialchars($student['username']) . '" and ' . $deleted_entries . ' wellness record(s) deleted successfully.';
        } else {
            mysqli_rollback($conn);
            $error = 'Failed to delete student account.';
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = 'Error deleting student: ' . htmlspecialchars($e->getMessage());
    }
}

if ($error) {
    $_SESSION['admin_error'] = $error;
}

header('Location: ../frontend/admin_dashboard.php');
exit;
