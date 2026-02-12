<?php
/**
 * Admin Dashboard - overall averages, monthly comparison charts, student data table
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../backend/config/db_connect.php';

// Total students
$r = mysqli_query($conn, 'SELECT COUNT(*) AS cnt FROM users WHERE role = "student"');
$total_students = (int) mysqli_fetch_assoc($r)['cnt'];

// Overall averages (rule-based: sum ÷ count)
$r = mysqli_query($conn,
    'SELECT AVG(stress_level) AS avg_stress, AVG(sleep_hours) AS avg_sleep
     FROM wellness_data');
$avgs = mysqli_fetch_assoc($r);
$avg_stress = $avgs['avg_stress'] !== null ? round((float)$avgs['avg_stress'], 1) : 0;
$avg_sleep = $avgs['avg_sleep'] !== null ? round((float)$avgs['avg_sleep'], 1) : 0;

// Monthly data for charts (last 6 months)
$months_labels = [];
$months_sleep = [];
$months_study = [];
for ($i = 5; $i >= 0; $i--) {
    $month_start = date('Y-m-01', strtotime("-$i months"));
    $month_end = date('Y-m-t', strtotime($month_start));
    $months_labels[] = date('M Y', strtotime($month_start));

    $stmt = mysqli_prepare($conn,
        'SELECT AVG(sleep_hours) AS s, AVG(study_hours) AS st
         FROM wellness_data WHERE entry_date BETWEEN ? AND ?');
    mysqli_stmt_bind_param($stmt, 'ss', $month_start, $month_end);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    $months_sleep[] = $row && $row['s'] !== null ? round((float)$row['s'], 1) : 0;
    $months_study[] = $row && $row['st'] !== null ? round((float)$row['st'], 1) : 0;
}

// Student data table: latest entry per user (or aggregate)
$result = mysqli_query($conn,
    'SELECT u.id, u.username,
        (SELECT sleep_hours FROM wellness_data w WHERE w.user_id = u.id ORDER BY entry_date DESC LIMIT 1) AS sleep_hrs,
        (SELECT study_hours FROM wellness_data w WHERE w.user_id = u.id ORDER BY entry_date DESC LIMIT 1) AS study_hrs,
        (SELECT activity_minutes FROM wellness_data w WHERE w.user_id = u.id ORDER BY entry_date DESC LIMIT 1) AS activity_mins,
        (SELECT stress_level FROM wellness_data w WHERE w.user_id = u.id ORDER BY entry_date DESC LIMIT 1) AS stress_level
     FROM users u WHERE u.role = "student"
     ORDER BY u.username');
$student_rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $student_rows[] = [
        'name' => $row['username'],
        'sleep_hrs' => $row['sleep_hrs'] !== null ? $row['sleep_hrs'] : '-',
        'study_hrs' => $row['study_hrs'] !== null ? $row['study_hrs'] : '-',
        'activity_mins' => $row['activity_mins'] !== null ? $row['activity_mins'] : '-',
        'stress_level' => $row['stress_level'] !== null ? $row['stress_level'] : '-'
    ];
}

$page_title = 'Admin Dashboard';
$current_page = 'admin_dashboard';
include 'includes/header.php';
?>

<div class="content-inner">
    <div class="summary-cards admin-cards">
        <div class="card card-green">
            <div class="card-title">Total Students</div>
            <div class="card-value"><?php echo $total_students; ?></div>
        </div>
        <div class="card card-blue">
            <div class="card-title">Average Stress</div>
            <div class="card-value"><?php echo $avg_stress; ?></div>
        </div>
        <div class="card card-orange">
            <div class="card-title">Avg Sleep Hours</div>
            <div class="card-value"><?php echo $avg_sleep; ?> hrs</div>
        </div>
    </div>

    <h3 class="section-title">Overall Wellness Overview</h3>
    <div class="charts-row">
        <div class="chart-card">
            <h3>Monthly Sleep Average</h3>
            <canvas id="chartAdminBar" height="200"></canvas>
        </div>
        <div class="chart-card">
            <h3>Monthly Study Average</h3>
            <canvas id="chartAdminLine" height="200"></canvas>
        </div>
    </div>

    <div class="table-card" id="records">
        <h3>Student Data</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Sleep Hrs</th>
                    <th>Study Hrs</th>
                    <th>Activity (mins)</th>
                    <th>Stress Level</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($student_rows)): ?>
                    <tr><td colspan="5">No student data yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($student_rows as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['name']); ?></td>
                            <td><?php echo htmlspecialchars($s['sleep_hrs']); ?></td>
                            <td><?php echo htmlspecialchars($s['study_hrs']); ?></td>
                            <td><?php echo htmlspecialchars($s['activity_mins']); ?></td>
                            <td><?php echo htmlspecialchars($s['stress_level']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
window.adminDashboardData = {
    months: <?php echo json_encode($months_labels); ?>,
    sleepData: <?php echo json_encode($months_sleep); ?>,
    studyData: <?php echo json_encode($months_study); ?>
};
</script>

<?php include 'includes/footer.php'; ?>
