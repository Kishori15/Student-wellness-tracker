<?php
/**
 * Student Dashboard - summary cards, study/sleep charts, mood trend graph, mood distribution.
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

require_once '../backend/config/db_connect.php';

$user_id = (int) $_SESSION['user_id'];

$today = date('Y-m-d');
$week_ago = date('Y-m-d', strtotime('-7 days'));
$ninety_days_ago = date('Y-m-d', strtotime('-90 days'));

$has_mood_col = _dashboard_has_mood_column($conn);

// Fetch wellness data for this week (summary + study/sleep by day)
$stmt = mysqli_prepare($conn,
    'SELECT entry_date, sleep_hours, study_hours, activity_minutes, stress_level' . ($has_mood_col ? ', mood' : '') . '
     FROM wellness_data
     WHERE user_id = ? AND entry_date BETWEEN ? AND ?
     ORDER BY entry_date');
mysqli_stmt_bind_param($stmt, 'iss', $user_id, $week_ago, $today);
mysqli_stmt_execute($stmt);
$rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Mood data for last 90 days (for Mood Trend graph and filters)
$stmt_mood = mysqli_prepare($conn,
    'SELECT entry_date, ' . ($has_mood_col ? 'mood' : 'stress_level AS mood') . '
     FROM wellness_data
     WHERE user_id = ? AND entry_date BETWEEN ? AND ?
     ORDER BY entry_date');
mysqli_stmt_bind_param($stmt_mood, 'iss', $user_id, $ninety_days_ago, $today);
mysqli_stmt_execute($stmt_mood);
$mood_rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt_mood), MYSQLI_ASSOC);
mysqli_stmt_close($stmt_mood);

// Normalize mood to 1–3 (map old stress 4,5 -> 3; 3->2; 1,2->1)
$mood_data = [];
foreach ($mood_rows as $r) {
    $m = (int) $r['mood'];
    if ($m > 3) $m = 3;
    if ($m < 1) $m = 1;
    $mood_data[] = ['date' => $r['entry_date'], 'mood' => $m];
}

$count = count($rows);
$sum_sleep = 0;
$sum_study = 0;
$sum_activity = 0;
$sum_mood = 0;
$by_day = [];

foreach (range(1, 7) as $d) {
    $by_day[$d] = ['study' => 0, 'sleep' => 0];
}

foreach ($rows as $r) {
    $sum_sleep += (float) $r['sleep_hours'];
    $sum_study += (float) $r['study_hours'];
    $sum_activity += (int) $r['activity_minutes'];
    $m = isset($r['mood']) ? (int) $r['mood'] : (int) $r['stress_level'];
    if ($m > 3) $m = 3;
    if ($m < 1) $m = 1;
    $sum_mood += $m;

    $dow = (int) date('N', strtotime($r['entry_date']));
    $by_day[$dow]['study'] += (float) $r['study_hours'];
    $by_day[$dow]['sleep'] += (float) $r['sleep_hours'];
}

$avg_sleep = $count > 0 ? round($sum_sleep / $count, 1) : 0;
$total_study = round($sum_study, 0);
$total_activity = $sum_activity;
$avg_mood = $count > 0 ? round($sum_mood / $count, 1) : 2;
$mood_label = $avg_mood >= 2.5 ? '😊 Happy' : ($avg_mood >= 1.5 ? '😐 Neutral' : '😞 Sad');

// Mood distribution (1=Sad, 2=Neutral, 3=Happy)
$mood_sad = 0;
$mood_neutral = 0;
$mood_happy = 0;
foreach ($rows as $r) {
    $m = isset($r['mood']) ? (int) $r['mood'] : (int) $r['stress_level'];
    if ($m > 3) $m = 3;
    if ($m < 1) $m = 1;
    if ($m == 1) $mood_sad++;
    elseif ($m == 2) $mood_neutral++;
    else $mood_happy++;
}

$days_order = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$study_by_day = [];
$sleep_by_day = [];
foreach ([1, 2, 3, 4, 5, 6, 7] as $d) {
    $study_by_day[] = $by_day[$d]['study'];
    $sleep_by_day[] = $by_day[$d]['sleep'];
}

function _dashboard_has_mood_column($conn) {
    $r = @mysqli_query($conn, "SHOW COLUMNS FROM wellness_data LIKE 'mood'");
    return $r && mysqli_num_rows($r) > 0;
}

// Goals: fetch if table exists
$goals = null;
$goals_progress = ['sleep' => null, 'study' => null, 'activity' => null];
$r = @mysqli_query($conn, "SHOW TABLES LIKE 'student_goals'");
if ($r && mysqli_num_rows($r) > 0) {
    $stmt = mysqli_prepare($conn, 'SELECT sleep_goal, study_goal, activity_goal FROM student_goals WHERE user_id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($row) {
        $goals = [
            'sleep_goal' => (float) $row['sleep_goal'],
            'study_goal' => (float) $row['study_goal'],
            'activity_goal' => $row['activity_goal'] !== null ? (int) $row['activity_goal'] : null
        ];
        $avg_sleep_week = $count > 0 ? $sum_sleep / $count : 0;
        $avg_study_per_day = $count > 0 ? $sum_study / $count : 0;
        $avg_activity_week = $count > 0 ? $sum_activity / $count : 0;
        $sleep_pct = $goals['sleep_goal'] > 0 ? min(100, round(($avg_sleep_week / $goals['sleep_goal']) * 100, 0)) : 0;
        $study_pct = $goals['study_goal'] > 0 ? min(100, round(($avg_study_per_day / $goals['study_goal']) * 100, 0)) : 0;
        $goals_progress['sleep'] = ['pct' => $sleep_pct, 'actual' => round($avg_sleep_week, 1), 'goal' => $goals['sleep_goal'], 'status' => $avg_sleep_week >= $goals['sleep_goal'] ? 'Completed' : ($sleep_pct >= 50 ? 'In Progress' : 'Not Achieved')];
        $goals_progress['study'] = ['pct' => $study_pct, 'actual' => round($avg_study_per_day, 1), 'goal' => $goals['study_goal'], 'status' => $avg_study_per_day >= $goals['study_goal'] ? 'Completed' : ($study_pct >= 50 ? 'In Progress' : 'Not Achieved')];
        if ($goals['activity_goal'] !== null && $goals['activity_goal'] > 0) {
            $activity_pct = min(100, round(($avg_activity_week / $goals['activity_goal']) * 100, 0));
            $goals_progress['activity'] = ['pct' => $activity_pct, 'actual' => round($avg_activity_week, 0), 'goal' => $goals['activity_goal'], 'status' => $avg_activity_week >= $goals['activity_goal'] ? 'Completed' : ($activity_pct >= 50 ? 'In Progress' : 'Not Achieved')];
        }
    }
}

$page_title = 'Dashboard';
$current_page = 'dashboard';
include 'includes/header.php';
?>

<div class="content-inner">
    <?php if ($goals !== null): ?>
    <div class="goals-progress-section">
        <h3 class="section-title">Goal Progress (This Week) <a href="goals.php" class="link-edit">Edit goals</a></h3>
        <div class="goals-progress-cards">
            <div class="goal-card">
                <div class="goal-label">Sleep: <?php echo $goals_progress['sleep']['actual']; ?> / <?php echo $goals_progress['sleep']['goal']; ?> hrs</div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar" style="width:<?php echo min(100, $goals_progress['sleep']['pct']); ?>%"></div>
                </div>
                <div class="goal-meta">
                    <span class="goal-pct"><?php echo $goals_progress['sleep']['pct']; ?>%</span>
                    <span class="goal-status status-<?php echo strtolower(str_replace(' ', '-', $goals_progress['sleep']['status'])); ?>"><?php echo $goals_progress['sleep']['status']; ?></span>
                </div>
            </div>
            <div class="goal-card">
                <div class="goal-label">Study: <?php echo $goals_progress['study']['actual']; ?> / <?php echo $goals_progress['study']['goal']; ?> hrs/day avg</div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar" style="width:<?php echo min(100, $goals_progress['study']['pct']); ?>%"></div>
                </div>
                <div class="goal-meta">
                    <span class="goal-pct"><?php echo $goals_progress['study']['pct']; ?>%</span>
                    <span class="goal-status status-<?php echo strtolower(str_replace(' ', '-', $goals_progress['study']['status'])); ?>"><?php echo $goals_progress['study']['status']; ?></span>
                </div>
            </div>
            <?php if ($goals_progress['activity'] !== null): ?>
            <div class="goal-card">
                <div class="goal-label">Activity: <?php echo $goals_progress['activity']['actual']; ?> / <?php echo $goals_progress['activity']['goal']; ?> mins avg</div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar" style="width:<?php echo min(100, $goals_progress['activity']['pct']); ?>%"></div>
                </div>
                <div class="goal-meta">
                    <span class="goal-pct"><?php echo $goals_progress['activity']['pct']; ?>%</span>
                    <span class="goal-status status-<?php echo strtolower(str_replace(' ', '-', $goals_progress['activity']['status'])); ?>"><?php echo $goals_progress['activity']['status']; ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="goals-cta">
        <p>Set your daily goals to track progress on the dashboard. <a href="goals.php">Set goals &rarr;</a></p>
    </div>
    <?php endif; ?>

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
            <div class="card-title">Mood</div>
            <div class="card-value value-green"><?php echo $mood_label; ?></div>
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
        <div class="chart-card chart-card-full">
            <h3>Mood Trend</h3>
            <div class="mood-filters">
                <button type="button" class="btn-filter active" data-range="7">Last 7 days</button>
                <button type="button" class="btn-filter" data-range="30">Last 30 days</button>
                <button type="button" class="btn-filter" data-range="custom">Custom range</button>
                <span class="custom-range" style="display:none;">
                    <input type="date" id="moodDateFrom" value="<?php echo date('Y-m-d', strtotime('-7 days')); ?>">
                    <label>to</label>
                    <input type="date" id="moodDateTo" value="<?php echo date('Y-m-d'); ?>">
                    <button type="button" class="btn-filter-apply" id="moodCustomApply">Apply</button>
                </span>
            </div>
            <canvas id="chartMoodTrend" height="200"></canvas>
        </div>
    </div>

    <div class="charts-row">
        <div class="chart-card">
            <h3>Wellness Score</h3>
            <canvas id="chartWellnessGauge" height="200"></canvas>
        </div>
        <div class="chart-card">
            <h3>Mood Distribution</h3>
            <canvas id="chartMoodPie" height="200"></canvas>
        </div>
    </div>
</div>

<script>
window.studentDashboardData = {
    days: <?php echo json_encode($days_order); ?>,
    studyHours: <?php echo json_encode($study_by_day); ?>,
    sleepHours: <?php echo json_encode($sleep_by_day); ?>,
    moodData: <?php echo json_encode($mood_data); ?>,
    moodDistribution: {
        sad: <?php echo $mood_sad; ?>,
        neutral: <?php echo $mood_neutral; ?>,
        happy: <?php echo $mood_happy; ?>
    }
};
</script>

<?php include 'includes/footer.php'; ?>
