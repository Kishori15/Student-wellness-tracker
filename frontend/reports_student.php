<?php
/**
 * Student: Weekly & Monthly Reports with improvement % (current vs previous period).
 * Period: Last 7 days (Weekly), Last 30 days (Monthly), Custom range.
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

require_once '../backend/config/db_connect.php';

$user_id = (int) $_SESSION['user_id'];
$today = date('Y-m-d');
$has_mood = _reports_has_mood_column($conn);

$period = isset($_GET['period']) ? $_GET['period'] : '7';
$custom_from = isset($_GET['from']) ? trim($_GET['from']) : '';
$custom_to   = isset($_GET['to']) ? trim($_GET['to']) : '';

if ($period === 'custom' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $custom_from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $custom_to) && $custom_from <= $custom_to) {
    $range_start = $custom_from;
    $range_end   = $custom_to;
    $prev_start  = null;
    $prev_end    = null;
    $days_prev   = max(1, (strtotime($custom_to) - strtotime($custom_from)) / 86400);
    $prev_end    = date('Y-m-d', strtotime($custom_from) - 86400);
    $prev_start  = date('Y-m-d', strtotime($prev_end) - ($days_prev * 86400));
} elseif ($period === '30') {
    $range_end   = $today;
    $range_start = date('Y-m-d', strtotime('-29 days'));
    $prev_end    = date('Y-m-d', strtotime($range_start) - 86400);
    $prev_start  = date('Y-m-d', strtotime('-59 days'));
} else {
    $period = '7';
    $range_end   = $today;
    $range_start = date('Y-m-d', strtotime('-6 days'));
    $prev_end    = date('Y-m-d', strtotime($range_start) - 86400);
    $prev_start  = date('Y-m-d', strtotime('-13 days'));
}

function fetch_period_stats($conn, $user_id, $from, $to, $has_mood) {
    $stmt = mysqli_prepare($conn,
        'SELECT AVG(sleep_hours) AS avg_sleep, SUM(study_hours) AS total_study, AVG(activity_minutes) AS avg_activity, ' .
        ($has_mood ? 'AVG(mood) AS avg_mood' : 'AVG(stress_level) AS avg_mood') . '
         FROM wellness_data WHERE user_id = ? AND entry_date BETWEEN ? AND ?');
    mysqli_stmt_bind_param($stmt, 'iss', $user_id, $from, $to);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$row) return ['avg_sleep' => 0, 'total_study' => 0, 'avg_mood' => 2, 'avg_activity' => 0];
    $m = (float)($row['avg_mood'] ?? 2);
    if ($m > 3) $m = 3;
    if ($m < 1) $m = 1;
    return [
        'avg_sleep'    => $row['avg_sleep'] !== null ? round((float)$row['avg_sleep'], 1) : 0,
        'total_study'  => $row['total_study'] !== null ? round((float)$row['total_study'], 1) : 0,
        'avg_mood'     => round($m, 1),
        'avg_activity' => $row['avg_activity'] !== null ? round((float)$row['avg_activity'], 0) : 0
    ];
}

function fetch_daily_for_chart($conn, $user_id, $from, $to, $has_mood) {
    $stmt = mysqli_prepare($conn,
        'SELECT entry_date, sleep_hours, study_hours, ' . ($has_mood ? 'mood' : 'stress_level AS mood') . '
         FROM wellness_data WHERE user_id = ? AND entry_date BETWEEN ? AND ?
         ORDER BY entry_date');
    mysqli_stmt_bind_param($stmt, 'iss', $user_id, $from, $to);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    $out = ['labels' => [], 'sleep' => [], 'study' => [], 'mood' => []];
    foreach ($rows as $r) {
        $out['labels'][] = $r['entry_date'];
        $out['sleep'][]  = (float)$r['sleep_hours'];
        $out['study'][] = (float)$r['study_hours'];
        $m = (int)($r['mood'] ?? 2);
        if ($m > 3) $m = 3;
        if ($m < 1) $m = 1;
        $out['mood'][]  = $m;
    }
    return $out;
}

$current = fetch_period_stats($conn, $user_id, $range_start, $range_end, $has_mood);
$previous = ($prev_start && $prev_end) ? fetch_period_stats($conn, $user_id, $prev_start, $prev_end, $has_mood) : null;

function pct_change($curr, $prev) {
    if ($prev == 0) return $curr > 0 ? 100 : 0;
    return round((($curr - $prev) / $prev) * 100, 1);
}

$pct_sleep   = $previous !== null ? pct_change($current['avg_sleep'], $previous['avg_sleep']) : null;
$pct_study   = $previous !== null ? pct_change($current['total_study'], $previous['total_study']) : null;
$pct_mood    = $previous !== null ? pct_change($current['avg_mood'], $previous['avg_mood']) : null;
$pct_activity= $previous !== null ? pct_change($current['avg_activity'], $previous['avg_activity']) : null;

$chart_data = fetch_daily_for_chart($conn, $user_id, $range_start, $range_end, $has_mood);

function _reports_has_mood_column($conn) {
    $r = @mysqli_query($conn, "SHOW COLUMNS FROM wellness_data LIKE 'mood'");
    return $r && mysqli_num_rows($r) > 0;
}

$page_title = 'Weekly & Monthly Reports';
$current_page = 'reports_student';
include 'includes/header.php';
?>

<div class="content-inner">
    <div class="page-banner">
        <h2>Weekly & Monthly Reports</h2>
    </div>

    <div class="report-period-filters">
        <a href="reports_student.php?period=7" class="btn-filter <?php echo $period === '7' ? 'active' : ''; ?>">Last 7 days (Weekly)</a>
        <a href="reports_student.php?period=30" class="btn-filter <?php echo $period === '30' ? 'active' : ''; ?>">Last 30 days (Monthly)</a>
        <span class="custom-range-inline">
            <form method="get" action="reports_student.php" class="inline-form">
                <input type="hidden" name="period" value="custom">
                <input type="date" name="from" value="<?php echo $period === 'custom' ? htmlspecialchars($custom_from) : date('Y-m-d', strtotime('-7 days')); ?>">
                <span>to</span>
                <input type="date" name="to" value="<?php echo $period === 'custom' ? htmlspecialchars($custom_to) : $today; ?>">
                <button type="submit" class="btn-filter-apply">Apply</button>
            </form>
        </span>
    </div>

    <div class="summary-cards">
        <div class="card">
            <div class="card-title">Average Sleep</div>
            <div class="card-value value-green"><?php echo $current['avg_sleep']; ?> hrs</div>
            <?php if ($pct_sleep !== null): ?>
                <div class="card-change <?php echo $pct_sleep >= 0 ? 'change-up' : 'change-down'; ?>">
                    <?php echo $pct_sleep >= 0 ? '+' : ''; ?><?php echo $pct_sleep; ?>% vs previous period
                </div>
            <?php endif; ?>
        </div>
        <div class="card">
            <div class="card-title">Average Mood</div>
            <div class="card-value value-green"><?php echo $current['avg_mood']; ?> / 3</div>
            <?php if ($pct_mood !== null): ?>
                <div class="card-change <?php echo $pct_mood >= 0 ? 'change-up' : 'change-down'; ?>">
                    <?php echo $pct_mood >= 0 ? '+' : ''; ?><?php echo $pct_mood; ?>% vs previous period
                </div>
            <?php endif; ?>
        </div>
        <div class="card">
            <div class="card-title">Total Study Hours</div>
            <div class="card-value"><?php echo $current['total_study']; ?> hrs</div>
            <?php if ($pct_study !== null): ?>
                <div class="card-change <?php echo $pct_study >= 0 ? 'change-up' : 'change-down'; ?>">
                    <?php echo $pct_study >= 0 ? '+' : ''; ?><?php echo $pct_study; ?>% vs previous period
                </div>
            <?php endif; ?>
        </div>
        <div class="card">
            <div class="card-title">Avg Activity (mins)</div>
            <div class="card-value"><?php echo $current['avg_activity']; ?></div>
            <?php if ($pct_activity !== null): ?>
                <div class="card-change <?php echo $pct_activity >= 0 ? 'change-up' : 'change-down'; ?>">
                    <?php echo $pct_activity >= 0 ? '+' : ''; ?><?php echo $pct_activity; ?>% vs previous period
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="charts-row">
        <div class="chart-card">
            <h3>Sleep by Date</h3>
            <canvas id="chartReportSleep" height="200"></canvas>
        </div>
        <div class="chart-card">
            <h3>Study Hours by Date</h3>
            <canvas id="chartReportStudy" height="200"></canvas>
        </div>
    </div>
    <div class="charts-row">
        <div class="chart-card">
            <h3>Mood by Date</h3>
            <canvas id="chartReportMood" height="200"></canvas>
        </div>
    </div>
</div>

<script>
window.reportsStudentData = {
    labels: <?php echo json_encode($chart_data['labels']); ?>,
    sleep: <?php echo json_encode($chart_data['sleep']); ?>,
    study: <?php echo json_encode($chart_data['study']); ?>,
    mood: <?php echo json_encode($chart_data['mood']); ?>
};
</script>
<script src="assets/js/reports_student.js"></script>

<?php include 'includes/footer.php'; ?>
