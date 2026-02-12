<?php
/**
 * Add Wellness Data - form: sleep hours, study hours, physical activity, stress level (1-5).
 * Receive via $_POST, validate, insert into MySQL.
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

require_once '../backend/config/db_connect.php';

$user_id = (int) $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sleep_hours = isset($_POST['sleep_hours']) ? trim($_POST['sleep_hours']) : '';
    $study_hours = isset($_POST['study_hours']) ? trim($_POST['study_hours']) : '';
    $activity_minutes = isset($_POST['activity_minutes']) ? trim($_POST['activity_minutes']) : '';
    $stress_level = isset($_POST['stress_level']) ? trim($_POST['stress_level']) : '';
    $entry_date = isset($_POST['entry_date']) ? trim($_POST['entry_date']) : date('Y-m-d');

    // Input validation
    $errs = [];
    if ($sleep_hours === '' || !is_numeric($sleep_hours) || (float)$sleep_hours < 0 || (float)$sleep_hours > 24) {
        $errs[] = 'Sleep hours must be a number between 0 and 24.';
    }
    if ($study_hours === '' || !is_numeric($study_hours) || (float)$study_hours < 0 || (float)$study_hours > 24) {
        $errs[] = 'Study hours must be a number between 0 and 24.';
    }
    if ($activity_minutes === '' || !ctype_digit($activity_minutes) || (int)$activity_minutes < 0 || (int)$activity_minutes > 1440) {
        $errs[] = 'Physical activity must be whole minutes between 0 and 1440.';
    }
    if ($stress_level === '' || !in_array($stress_level, ['1', '2', '3', '4', '5'])) {
        $errs[] = 'Stress level must be 1, 2, 3, 4, or 5.';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $entry_date)) {
        $errs[] = 'Invalid entry date.';
    }

    if (!empty($errs)) {
        $error = implode(' ', $errs);
    } else {
        $stmt = mysqli_prepare($conn,
            'INSERT INTO wellness_data (user_id, sleep_hours, study_hours, activity_minutes, stress_level, entry_date)
             VALUES (?, ?, ?, ?, ?, ?)');
        $sh = (float) $sleep_hours;
        $sth = (float) $study_hours;
        $am = (int) $activity_minutes;
        $sl = (int) $stress_level;
        mysqli_stmt_bind_param($stmt, 'iddiis', $user_id, $sh, $sth, $am, $sl, $entry_date);

        if (mysqli_stmt_execute($stmt)) {
            $message = 'Wellness data saved successfully.';
        } else {
            $error = 'Could not save data. Please try again.';
        }
        mysqli_stmt_close($stmt);
    }
}

$page_title = 'Add Wellness Data';
$current_page = 'add_wellness';
include 'includes/header.php';
?>

<div class="content-inner">
    <div class="page-banner">
        <h2>Add Wellness Data</h2>
    </div>

    <div class="form-card">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="add_wellness.php">
            <div class="form-group">
                <label for="sleep_hours">Sleep Hours:</label>
                <input type="number" id="sleep_hours" name="sleep_hours" step="0.5" min="0" max="24" required
                       value="<?php echo htmlspecialchars($_POST['sleep_hours'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="study_hours">Study Hours:</label>
                <input type="number" id="study_hours" name="study_hours" step="0.5" min="0" max="24" required
                       value="<?php echo htmlspecialchars($_POST['study_hours'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="activity_minutes">Physical Activity (minutes):</label>
                <select id="activity_minutes" name="activity_minutes" required>
                    <option value="">Select</option>
                    <?php for ($m = 0; $m <= 180; $m += 15): ?>
                        <option value="<?php echo $m; ?>" <?php echo (isset($_POST['activity_minutes']) && (int)$_POST['activity_minutes'] === $m) ? 'selected' : ''; ?>><?php echo $m; ?> mins</option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="stress_level">Stress Level:</label>
                <select id="stress_level" name="stress_level" required>
                    <option value="">Select</option>
                    <option value="1" <?php echo (isset($_POST['stress_level']) && $_POST['stress_level'] === '1') ? 'selected' : ''; ?>>1 - Low</option>
                    <option value="2" <?php echo (isset($_POST['stress_level']) && $_POST['stress_level'] === '2') ? 'selected' : ''; ?>>2</option>
                    <option value="3" <?php echo (isset($_POST['stress_level']) && $_POST['stress_level'] === '3') ? 'selected' : ''; ?>>3 - Moderate</option>
                    <option value="4" <?php echo (isset($_POST['stress_level']) && $_POST['stress_level'] === '4') ? 'selected' : ''; ?>>4</option>
                    <option value="5" <?php echo (isset($_POST['stress_level']) && $_POST['stress_level'] === '5') ? 'selected' : ''; ?>>5 - High</option>
                </select>
            </div>
            <div class="form-group">
                <label for="entry_date">Entry Date:</label>
                <input type="date" id="entry_date" name="entry_date" value="<?php echo htmlspecialchars($_POST['entry_date'] ?? date('Y-m-d')); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
