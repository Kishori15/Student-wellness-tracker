<?php
/**
 * Admin: View Individual Student Wellness Records
 * RBAC: Admin only
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../backend/login.php');
    exit;
}

require_once '../backend/config/db_connect.php';

$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

if ($student_id <= 0) {
    header('Location: admin_dashboard.php');
    exit;
}

// Verify student exists and is actually a student
$stmt = mysqli_prepare($conn, 'SELECT id, username FROM users WHERE id = ? AND role = "student"');
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$student) {
    $error = 'Student not found.';
} else {
    $has_mood_col = _admin_has_mood_column($conn);
    
    // Fetch all wellness records for this student
    $stmt = mysqli_prepare($conn,
        'SELECT id, entry_date, sleep_hours, study_hours, activity_minutes, stress_level' . 
        ($has_mood_col ? ', mood' : '') . '
         FROM wellness_data 
         WHERE user_id = ? 
         ORDER BY entry_date DESC');
    mysqli_stmt_bind_param($stmt, 'i', $student_id);
    mysqli_stmt_execute($stmt);
    $entries = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    
    // Calculate averages
    $n = count($entries);
    $avg_sleep = $n ? round(array_sum(array_column($entries, 'sleep_hours')) / $n, 1) : 0;
    $avg_study = $n ? round(array_sum(array_column($entries, 'study_hours')) / $n, 1) : 0;
    $avg_activity = $n ? round(array_sum(array_column($entries, 'activity_minutes')) / $n, 0) : 0;
    
    // Mood labels
    $mood_emoji = [1 => '😞 Sad', 2 => '😐 Neutral', 3 => '😊 Happy'];
    foreach ($entries as &$e) {
        $m = isset($e['mood']) ? (int)$e['mood'] : (int)$e['stress_level'];
        if ($m > 3) $m = 3;
        if ($m < 1) $m = 1;
        $e['mood_label'] = $mood_emoji[$m];
    }
}

function _admin_has_mood_column($conn) {
    $r = @mysqli_query($conn, "SHOW COLUMNS FROM wellness_data LIKE 'mood'");
    return $r && mysqli_num_rows($r) > 0;
}

// Check for messages from deletion actions
$admin_message = isset($_SESSION['admin_message']) ? $_SESSION['admin_message'] : '';
$admin_error = isset($_SESSION['admin_error']) ? $_SESSION['admin_error'] : '';
unset($_SESSION['admin_message'], $_SESSION['admin_error']);

$page_title = 'View Student Records';
$current_page = 'admin_dashboard';
include 'includes/header.php';
?>

<div class="content-inner">
    <div class="page-banner">
        <h2>Student Wellness Records: <?php echo htmlspecialchars($student['username'] ?? 'Unknown'); ?></h2>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <p><a href="admin_dashboard.php">← Back to Dashboard</a></p>
    <?php elseif ($student): ?>
        <?php if ($admin_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($admin_message); ?></div>
        <?php endif; ?>
        <?php if ($admin_error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($admin_error); ?></div>
        <?php endif; ?>

        <div class="summary-cards admin-cards">
            <div class="card card-green">
                <div class="card-title">Total Entries</div>
                <div class="card-value"><?php echo $n; ?></div>
            </div>
            <div class="card card-blue">
                <div class="card-title">Avg Sleep</div>
                <div class="card-value"><?php echo $avg_sleep; ?> hrs</div>
            </div>
            <div class="card card-orange">
                <div class="card-title">Avg Study</div>
                <div class="card-value"><?php echo $avg_study; ?> hrs</div>
            </div>
            <div class="card">
                <div class="card-title">Avg Activity</div>
                <div class="card-value"><?php echo $avg_activity; ?> mins</div>
            </div>
        </div>

        <div class="table-card">
            <h3>Wellness Records</h3>
            <?php if (empty($entries)): ?>
                <p>No wellness records found for this student.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Sleep Hrs</th>
                            <th>Study Hrs</th>
                            <th>Activity (mins)</th>
                            <th>Mood</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $e): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($e['entry_date']); ?></td>
                                <td><?php echo htmlspecialchars($e['sleep_hours']); ?></td>
                                <td><?php echo htmlspecialchars($e['study_hours']); ?></td>
                                <td><?php echo htmlspecialchars($e['activity_minutes']); ?></td>
                                <td><?php echo $e['mood_label']; ?></td>
                                <td class="action-buttons">
                                    <button onclick="confirmDeleteEntry(<?php echo $e['id']; ?>, '<?php echo htmlspecialchars($e['entry_date']); ?>')" class="btn-action btn-delete-small" title="Delete Entry">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <p style="margin-top: 20px;">
            <a href="admin_dashboard.php">← Back to Dashboard</a>
        </p>
    <?php endif; ?>
</div>

<script>
function confirmDeleteEntry(entryId, entryDate) {
    if (confirm('Are you sure you want to delete the wellness entry from ' + entryDate + '?\n\nThis action cannot be undone!')) {
        window.location.href = '../backend/admin_delete_entry.php?id=' + entryId + '&student_id=<?php echo $student_id; ?>';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
