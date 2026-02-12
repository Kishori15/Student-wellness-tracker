<?php
/**
 * Student Dashboard - summary cards, study hours bar chart, sleep trend line chart,
 * stress distribution pie chart. Rule-based calculations only.
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

require_once '../backend/config/db_connect.php';

$user_id = (int) $_SESSION['user_id'];

// Rule-based: weekly = last 7 days
$today = date('Y-m-d');
$week_ago = date('Y-m-d', strtotime('-7 days'));

// Fetch wellness data for this week
$stmt = mysqli_prepare($conn,
    'SELECT entry_date, sleep_hours, study_hours, activity_minutes, stress_level
     FROM wellness_data
     WHERE user_id = ? AND entry_date BETWEEN ? AND ?
     ORDER BY entry_date');
mysqli_stmt_bind_param($stmt, 'iss', $user_id, $week_ago, $today);
mysqli_stmt_execute($stmt);
$rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Rule-based calculations: weekly averages = sum ÷ count
$count = count($rows);
$sum_sleep = 0;
$sum_study = 0;
$sum_activity = 0;
$sum_stress = 0;
$by_day = [];
$day_names = ['Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6, 'Sun' => 7];

foreach (range(1, 7) as $d) {
    $by_day[$d] = ['study' => 0, 'sleep' => 0];
}

foreach ($rows as $r) {
    $sum_sleep += (float) $r['sleep_hours'];
    $sum_study += (float) $r['study_hours'];
    $sum_activity += (int) $r['activity_minutes'];
    $sum_stress += (int) $r['stress_level'];

    $dow = (int) date('N', strtotime($r['entry_date']));
    $by_day[$dow]['study'] += (float) $r['study_hours'];
    $by_day[$dow]['sleep'] += (float) $r['sleep_hours'];
}

$avg_sleep = $count > 0 ? round($sum_sleep / $count, 1) : 0;
$total_study = round($sum_study, 0);
$total_activity = $sum_activity;
$avg_stress = $count > 0 ? round($sum_stress / $count, 1) : 0;

// Rule-based: stress level label (1-5 scale)
if ($avg_stress >= 4) {
    $stress_label = 'High';
} elseif ($avg_stress >= 2.5) {
    $stress_label = 'Moderate';
} else {
    $stress_label = 'Low';
}

// Rule-based: stress distribution counts (1-2 Low, 3 Mode, 4-5 High)
$stress_low = 0;
$stress_mode = 0;
$stress_high = 0;
foreach ($rows as $r) {
    $s = (int) $r['stress_level'];
    if ($s <= 2) $stress_low++;
    elseif ($s == 3) $stress_mode++;
    else $stress_high++;
}

// Data for Chart.js: days of week
$days_order = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$study_by_day = [];
$sleep_by_day = [];
foreach ([1, 2, 3, 4, 5, 6, 7] as $d) {
    $study_by_day[] = $by_day[$d]['study'];
    $sleep_by_day[] = $by_day[$d]['sleep'];
}

$page_title = 'Dashboard';
$current_page = 'dashboard';
include 'includes/header.php';
?>

<div class="content-inner">
    <div class="summary-cards">
        <div class="card">
            <div class="card-title">Average Sleep</div>
            <div class="card-value value-green"><?php echo $avg_sleep; ?> hrs</div>
        </div>
        <div class="card">
            <div class="card-title">Total Study Hours</div>
            <div class="card-value"><?php echo $total_study; ?> hrs</div>
        </div>
        <div class="card">
            <div class="card-title">Stress Level</div>
            <div class="card-value value-green"><?php echo htmlspecialchars($stress_label); ?></div>
        </div>
        <div class="card">
            <div class="card-title">Physical Activity</div>
            <div class="card-value"><?php echo $total_activity; ?> mins</div>
        </div>
    </div>

    <div class="charts-row">
        <div class="chart-card">
            <h3>Study Hours This Week</h3>
            <canvas id="chartStudyHours" height="200"></canvas>
        </div>
        <div class="chart-card">
            <h3>Sleep Trend This Week</h3>
            <canvas id="chartSleepTrend" height="200"></canvas>
        </div>
    </div>

    <div class="charts-row">
        <div class="chart-card">
            <h3>Wellness Score</h3>
            <canvas id="chartWellnessGauge" height="200"></canvas>
        </div>
        <div class="chart-card">
            <h3>Stress Level Distribution</h3>
            <canvas id="chartStressPie" height="200"></canvas>
        </div>
    </div>
</div>

<script>
window.studentDashboardData = {
    days: <?php echo json_encode($days_order); ?>,
    studyHours: <?php echo json_encode($study_by_day); ?>,
    sleepHours: <?php echo json_encode($sleep_by_day); ?>,
    stressDistribution: {
        low: <?php echo $stress_low; ?>,
        moderate: <?php echo $stress_mode; ?>,
        high: <?php echo $stress_high; ?>
    }
};
</script>

<?php include 'includes/footer.php'; ?>
