<?php
/**
 * View Reports (student) - list personal wellness entries and summary (with mood)
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

require_once '../backend/config/db_connect.php';

$user_id = (int) $_SESSION['user_id'];

$has_mood = _view_has_mood_column($conn);
$stmt = mysqli_prepare($conn,
    'SELECT entry_date, sleep_hours, study_hours, activity_minutes, stress_level' . ($has_mood ? ', mood' : '') . '
     FROM wellness_data WHERE user_id = ? ORDER BY entry_date DESC LIMIT 100');
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$entries = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$mood_emoji = [1 => '😞 Sad', 2 => '😐 Neutral', 3 => '😊 Happy'];
foreach ($entries as &$e) {
    $m = isset($e['mood']) ? (int)$e['mood'] : (int)$e['stress_level'];
    if ($m > 3) $m = 3;
    if ($m < 1) $m = 1;
    $e['mood_label'] = $mood_emoji[$m];
}

$n = count($entries);
$avg_sleep = $n ? round(array_sum(array_column($entries, 'sleep_hours')) / $n, 1) : 0;
$avg_study = $n ? round(array_sum(array_column($entries, 'study_hours')) / $n, 1) : 0;
$avg_activity = $n ? round(array_sum(array_column($entries, 'activity_minutes')) / $n, 0) : 0;
$avg_mood = $n ? round(array_sum(array_map(function ($e) {
    $m = isset($e['mood']) ? (int)$e['mood'] : (int)$e['stress_level'];
    return $m > 3 ? 3 : ($m < 1 ? 1 : $m);
}, $entries)) / $n, 1) : 2;
$avg_mood_label = $mood_emoji[$avg_mood >= 2.5 ? 3 : ($avg_mood >= 1.5 ? 2 : 1)];

function _view_has_mood_column($conn) {
    $r = @mysqli_query($conn, "SHOW COLUMNS FROM wellness_data LIKE 'mood'");
    return $r && mysqli_num_rows($r) > 0;
}

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
            <div class="card-title">Avg Mood</div>
            <div class="card-value"><?php echo $avg_mood_label; ?></div>
        </div>
    </div>

    <div class="table-card">
        <h3>My Wellness Records</h3>
        <?php if (empty($entries)): ?>
            <p>No wellness data yet. <a href="check_in.php">Wellness Check-In</a> to add your first entry.</p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Sleep Hrs</th>
                        <th>Study Hrs</th>
                        <th>Activity (mins)</th>
                        <th>Mood</th>
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
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
