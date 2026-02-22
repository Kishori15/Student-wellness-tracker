<?php
/**
 * Student: Set or update daily goals (sleep, study, activity).
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

require_once '../backend/config/db_connect.php';

$user_id = (int) $_SESSION['user_id'];

$sleep_goal = 7.0;
$study_goal = 4.0;
$activity_goal = null;

$has_table = false;
$r = @mysqli_query($conn, "SHOW TABLES LIKE 'student_goals'");
if ($r && mysqli_num_rows($r) > 0) {
    $has_table = true;
    $stmt = mysqli_prepare($conn, 'SELECT sleep_goal, study_goal, activity_goal FROM student_goals WHERE user_id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($row) {
        $sleep_goal = (float) $row['sleep_goal'];
        $study_goal = (float) $row['study_goal'];
        $activity_goal = $row['activity_goal'] !== null ? (int) $row['activity_goal'] : null;
    }
}

$message = isset($_SESSION['goals_success']) ? $_SESSION['goals_success'] : '';
$error   = isset($_SESSION['goals_error']) ? $_SESSION['goals_error'] : '';
unset($_SESSION['goals_success'], $_SESSION['goals_error']);

$page_title = 'My Goals';
$current_page = 'goals';
include 'includes/header.php';
?>

<div class="content-inner">
    <div class="page-banner">
        <h2>Set Your Daily Goals</h2>
    </div>

    <?php if (!$has_table): ?>
        <div class="alert alert-error">Goals are not set up yet. Please ask your administrator to run database/sql/student_goals.sql</div>
    <?php else: ?>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="form-card">
            <p class="form-hint">Set daily targets. Your dashboard will show progress toward these goals.</p>
            <form method="post" action="../backend/goals_save.php">
                <div class="form-group">
                    <label for="sleep_goal">Sleep goal (hours):</label>
                    <input type="number" id="sleep_goal" name="sleep_goal" step="0.5" min="0" max="24" required
                           value="<?php echo htmlspecialchars($sleep_goal); ?>">
                </div>
                <div class="form-group">
                    <label for="study_goal">Study goal (hours):</label>
                    <input type="number" id="study_goal" name="study_goal" step="0.5" min="0" max="24" required
                           value="<?php echo htmlspecialchars($study_goal); ?>">
                </div>
                <div class="form-group">
                    <label for="activity_goal">Activity goal (minutes, optional):</label>
                    <input type="number" id="activity_goal" name="activity_goal" min="0" max="1440" step="5"
                           value="<?php echo $activity_goal !== null ? (int)$activity_goal : ''; ?>"
                           placeholder="Leave empty if not set">
                </div>
                <button type="submit" class="btn btn-primary">Save Goals</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
