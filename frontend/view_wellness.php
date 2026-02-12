<?php
/**
 * View Reports (student) - list personal wellness entries and summary
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

require_once '../backend/config/db_connect.php';

$user_id = (int) $_SESSION['user_id'];

$stmt = mysqli_prepare($conn,
    'SELECT entry_date, sleep_hours, study_hours, activity_minutes, stress_level
     FROM wellness_data WHERE user_id = ? ORDER BY entry_date DESC LIMIT 100');
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$entries = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Rule-based: averages
$n = count($entries);
$avg_sleep = $n ? round(array_sum(array_column($entries, 'sleep_hours')) / $n, 1) : 0;
$avg_study = $n ? round(array_sum(array_column($entries, 'study_hours')) / $n, 1) : 0;
$avg_activity = $n ? round(array_sum(array_column($entries, 'activity_minutes')) / $n, 0) : 0;
$avg_stress = $n ? round(array_sum(array_column($entries, 'stress_level')) / $n, 1) : 0;

$page_title = 'View Reports';
$current_page = 'view_wellness';
include 'includes/header.php';
?>

<div class="content-inner">
    <div class="page-banner">
        <h2>View Reports</h2>
    </div>

    <div class="summary-cards">
        <div class="card">
            <div class="card-title">Average Sleep</div>
            <div class="card-value"><?php echo $avg_sleep; ?> hrs</div>
        </div>
        <div class="card">
            <div class="card-title">Average Study</div>
            <div class="card-value"><?php echo $avg_study; ?> hrs</div>
        </div>
        <div class="card">
            <div class="card-title">Avg Activity</div>
            <div class="card-value"><?php echo $avg_activity; ?> mins</div>
        </div>
        <div class="card">
            <div class="card-title">Avg Stress</div>
            <div class="card-value"><?php echo $avg_stress; ?></div>
        </div>
    </div>

    <div class="table-card">
        <h3>My Wellness Records</h3>
        <?php if (empty($entries)): ?>
            <p>No wellness data yet. <a href="add_wellness.php">Add wellness data</a>.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Sleep Hrs</th>
                        <th>Study Hrs</th>
                        <th>Activity (mins)</th>
                        <th>Stress Level</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $e): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($e['entry_date']); ?></td>
                            <td><?php echo htmlspecialchars($e['sleep_hours']); ?></td>
                            <td><?php echo htmlspecialchars($e['study_hours']); ?></td>
                            <td><?php echo htmlspecialchars($e['activity_minutes']); ?></td>
                            <td><?php echo htmlspecialchars($e['stress_level']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
