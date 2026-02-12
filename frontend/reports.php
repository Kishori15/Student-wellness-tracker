<?php
/**
 * View Reports (admin) - overall campus report
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../backend/config/db_connect.php';

$r = mysqli_query($conn, 'SELECT COUNT(*) AS cnt FROM users WHERE role = "student"');
$total_students = (int) mysqli_fetch_assoc($r)['cnt'];

$r = mysqli_query($conn,
    'SELECT AVG(sleep_hours) AS s, AVG(study_hours) AS st, AVG(activity_minutes) AS a, AVG(stress_level) AS str
     FROM wellness_data');
$avgs = mysqli_fetch_assoc($r);
$avg_sleep = $avgs['s'] !== null ? round((float)$avgs['s'], 1) : 0;
$avg_study = $avgs['st'] !== null ? round((float)$avgs['st'], 1) : 0;
$avg_activity = $avgs['a'] !== null ? round((float)$avgs['a'], 0) : 0;
$avg_stress = $avgs['str'] !== null ? round((float)$avgs['str'], 1) : 0;

// Rule-based labels
$stress_label = $avg_stress >= 4 ? 'High' : ($avg_stress >= 2.5 ? 'Moderate' : 'Low');
$sleep_label = $avg_sleep < 6 ? 'Poor sleep' : 'OK';

$page_title = 'View Reports';
$current_page = 'reports';
include 'includes/header.php';
?>

<div class="content-inner">
    <div class="page-banner">
        <h2>Overall Campus Report</h2>
    </div>

    <div class="summary-cards admin-cards">
        <div class="card card-green">
            <div class="card-title">Total Students</div>
            <div class="card-value"><?php echo $total_students; ?></div>
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
        <div class="card">
            <div class="card-title">Avg Stress</div>
            <div class="card-value"><?php echo $avg_stress; ?> (<?php echo htmlspecialchars($stress_label); ?>)</div>
        </div>
    </div>

    <div class="report-card">
        <h3>Rule-based summary</h3>
        <ul>
            <li>Stress: <?php echo htmlspecialchars($stress_label); ?> (avg <?php echo $avg_stress; ?>)</li>
            <li>Sleep: <?php echo htmlspecialchars($sleep_label); ?> (avg <?php echo $avg_sleep; ?> hrs)</li>
            <li>Weekly/monthly averages computed as sum ÷ count (no AI).</li>
        </ul>
    </div>

    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
</div>

<?php include 'includes/footer.php'; ?>
