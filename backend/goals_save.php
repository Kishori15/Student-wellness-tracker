<?php
/**
 * Save or update student goals (sleep, study, activity).
 * RBAC: Student only. Prepared statements.
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../frontend/goals.php');
    exit;
}

require_once 'config/db_connect.php';

$user_id = (int) $_SESSION['user_id'];

$sleep_goal = isset($_POST['sleep_goal']) ? trim($_POST['sleep_goal']) : '';
$study_goal = isset($_POST['study_goal']) ? trim($_POST['study_goal']) : '';
$activity_goal = isset($_POST['activity_goal']) ? trim($_POST['activity_goal']) : '';

$errs = [];
if ($sleep_goal === '' || !is_numeric($sleep_goal) || (float)$sleep_goal < 0 || (float)$sleep_goal > 24) {
    $errs[] = 'Sleep goal must be between 0 and 24 hours.';
}
if ($study_goal === '' || !is_numeric($study_goal) || (float)$study_goal < 0 || (float)$study_goal > 24) {
    $errs[] = 'Study goal must be between 0 and 24 hours.';
}
if ($activity_goal !== '') {
    if (!ctype_digit($activity_goal) || (int)$activity_goal < 0 || (int)$activity_goal > 1440) {
        $errs[] = 'Activity goal must be 0–1440 minutes or leave empty.';
    }
}

if (!empty($errs)) {
    $_SESSION['goals_error'] = implode(' ', $errs);
    header('Location: ../frontend/goals.php');
    exit;
}

$sleep_goal_f = (float) $sleep_goal;
$study_goal_f = (float) $study_goal;
$activity_goal_int = $activity_goal === '' ? null : (int) $activity_goal;

$has_table = false;
$r = @mysqli_query($conn, "SHOW TABLES LIKE 'student_goals'");
if ($r && mysqli_num_rows($r) > 0) {
    $has_table = true;
}

if (!$has_table) {
    $_SESSION['goals_error'] = 'Goals feature is not set up. Please run database/sql/student_goals.sql.';
    header('Location: ../frontend/goals.php');
    exit;
}

$stmt = mysqli_prepare($conn, 'SELECT user_id FROM student_goals WHERE user_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$exists = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($exists) {
    if ($activity_goal_int === null) {
        $stmt = mysqli_prepare($conn, 'UPDATE student_goals SET sleep_goal = ?, study_goal = ?, activity_goal = NULL, updated_at = NOW() WHERE user_id = ?');
        mysqli_stmt_bind_param($stmt, 'ddi', $sleep_goal_f, $study_goal_f, $user_id);
    } else {
        $stmt = mysqli_prepare($conn, 'UPDATE student_goals SET sleep_goal = ?, study_goal = ?, activity_goal = ?, updated_at = NOW() WHERE user_id = ?');
        mysqli_stmt_bind_param($stmt, 'ddii', $sleep_goal_f, $study_goal_f, $activity_goal_int, $user_id);
    }
} else {
    if ($activity_goal_int === null) {
        $stmt = mysqli_prepare($conn, 'INSERT INTO student_goals (user_id, sleep_goal, study_goal, activity_goal) VALUES (?, ?, ?, NULL)');
        mysqli_stmt_bind_param($stmt, 'idd', $user_id, $sleep_goal_f, $study_goal_f);
    } else {
        $stmt = mysqli_prepare($conn, 'INSERT INTO student_goals (user_id, sleep_goal, study_goal, activity_goal) VALUES (?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'iddi', $user_id, $sleep_goal_f, $study_goal_f, $activity_goal_int);
    }
}

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['goals_success'] = 'Goals saved successfully.';
} else {
    $_SESSION['goals_error'] = 'Could not save goals. Please try again.';
}
mysqli_stmt_close($stmt);

header('Location: ../frontend/goals.php');
exit;
