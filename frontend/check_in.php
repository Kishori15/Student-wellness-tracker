<?php
/**
 * Wellness Check-In - Single source for all wellness data.
 * Mood, sleep, study, optional note. One submission per day.
 * All analytics, dashboards, and reports use this data.
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

require_once '../backend/config/db_connect.php';

$user_id = (int) $_SESSION['user_id'];
$today = date('Y-m-d');
$message = '';
$error = '';
$already_checked = false;

// Check if already checked in today
$stmt = mysqli_prepare($conn, 'SELECT id FROM wellness_data WHERE user_id = ? AND entry_date = ?');
mysqli_stmt_bind_param($stmt, 'is', $user_id, $today);
mysqli_stmt_execute($stmt);
$existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($existing) {
    $already_checked = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_checked) {
    $sleep_hours = isset($_POST['sleep_hours']) ? trim($_POST['sleep_hours']) : '';
    $study_hours = isset($_POST['study_hours']) ? trim($_POST['study_hours']) : '';
    $mood = isset($_POST['mood']) ? trim($_POST['mood']) : '';
    $reflection_note = isset($_POST['reflection_note']) ? trim($_POST['reflection_note']) : '';
    $activity_minutes = isset($_POST['activity_minutes']) ? (int)trim($_POST['activity_minutes']) : 0;

    $errs = [];
    if ($sleep_hours === '' || !is_numeric($sleep_hours) || (float)$sleep_hours < 0 || (float)$sleep_hours > 24) {
        $errs[] = 'Sleep hours must be 0-24.';
    }
    if ($study_hours === '' || !is_numeric($study_hours) || (float)$study_hours < 0 || (float)$study_hours > 24) {
        $errs[] = 'Study hours must be 0-24.';
    }
    if ($mood === '' || !in_array($mood, ['1', '2', '3'])) {
        $errs[] = 'Please select a mood.';
    }
    if ($activity_minutes < 0 || $activity_minutes > 1440) {
        $errs[] = 'Physical activity must be 0-1440 minutes.';
    }
    if (strlen($reflection_note) > 500) {
        $errs[] = 'Reflection note must be 500 characters or less.';
    }

    if (empty($errs)) {
        $has_reflection = _check_has_reflection_column($conn);
        $has_mood = _check_has_mood_column($conn);
        
        $mood_int = (int) $mood;
        $stress_level = $mood_int;
        $sh = (float) $sleep_hours;
        $sth = (float) $study_hours;
        
        if ($has_reflection && $has_mood) {
            $stmt = mysqli_prepare($conn,
                'INSERT INTO wellness_data (user_id, sleep_hours, study_hours, activity_minutes, stress_level, mood, entry_date, reflection_note)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $reflection_val = $reflection_note === '' ? null : $reflection_note;
            mysqli_stmt_bind_param($stmt, 'iddiiiss', $user_id, $sh, $sth, $activity_minutes, $stress_level, $mood_int, $today, $reflection_val);
        } elseif ($has_mood) {
            $stmt = mysqli_prepare($conn,
                'INSERT INTO wellness_data (user_id, sleep_hours, study_hours, activity_minutes, stress_level, mood, entry_date)
                 VALUES (?, ?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'iddiiis', $user_id, $sh, $sth, $activity_minutes, $stress_level, $mood_int, $today);
        } else {
            $stmt = mysqli_prepare($conn,
                'INSERT INTO wellness_data (user_id, sleep_hours, study_hours, activity_minutes, stress_level, entry_date)
                 VALUES (?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'iddiis', $user_id, $sh, $sth, $activity_minutes, $stress_level, $today);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['checkin_success'] = true;
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Could not save check-in. Please try again.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = implode(' ', $errs);
    }
}

function _check_has_reflection_column($conn) {
    $r = @mysqli_query($conn, "SHOW COLUMNS FROM wellness_data LIKE 'reflection_note'");
    return $r && mysqli_num_rows($r) > 0;
}

function _check_has_mood_column($conn) {
    $r = @mysqli_query($conn, "SHOW COLUMNS FROM wellness_data LIKE 'mood'");
    return $r && mysqli_num_rows($r) > 0;
}

$page_title = 'Wellness Check-In';
$current_page = 'check_in';
include 'includes/header.php';
?>

<div class="content-inner">
    <div class="checkin-banner">
        <h2>Wellness Check-In</h2>
        <p class="checkin-subtitle">Your single source for wellness data • One check-in per day • Private & secure 🔒</p>
    </div>

    <?php if ($already_checked): ?>
        <div class="checkin-card already-checked">
            <div class="checkin-icon">✓</div>
            <h3>You've already checked in today</h3>
            <p>Your data is used for all dashboards and reports. Come back tomorrow for your next check-in.</p>
            <a href="dashboard.php" class="btn btn-primary">View Dashboard</a>
        </div>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="check_in.php" class="checkin-form" id="checkinForm">
            <div class="checkin-card">
                <div class="checkin-section">
                    <label class="checkin-label">How are you feeling today? 😊</label>
                    <div class="mood-select-large">
                        <label class="mood-option-large">
                            <input type="radio" name="mood" value="3" required>
                            <div class="mood-emoji-large">😊</div>
                            <span class="mood-label-large">Happy</span>
                        </label>
                        <label class="mood-option-large">
                            <input type="radio" name="mood" value="2">
                            <div class="mood-emoji-large">😐</div>
                            <span class="mood-label-large">Neutral</span>
                        </label>
                        <label class="mood-option-large">
                            <input type="radio" name="mood" value="1">
                            <div class="mood-emoji-large">😞</div>
                            <span class="mood-label-large">Sad</span>
                        </label>
                    </div>
                </div>

                <div class="checkin-section">
                    <label class="checkin-label" for="sleep_hours">Sleep Hours (last night)</label>
                    <input type="number" id="sleep_hours" name="sleep_hours" step="0.5" min="0" max="24" required
                           class="checkin-input" placeholder="e.g., 7.5">
                </div>

                <div class="checkin-section">
                    <label class="checkin-label" for="study_hours">Study Hours (today)</label>
                    <input type="number" id="study_hours" name="study_hours" step="0.5" min="0" max="24" required
                           class="checkin-input" placeholder="e.g., 4">
                </div>

                <div class="checkin-section">
                    <label class="checkin-label" for="activity_minutes">Physical Activity (minutes today)</label>
                    <select id="activity_minutes" name="activity_minutes" class="checkin-input">
                        <?php for ($m = 0; $m <= 180; $m += 15): ?>
                            <option value="<?php echo $m; ?>"><?php echo $m; ?> mins</option>
                        <?php endfor; ?>
                    </select>
                    <small class="checkin-hint">Exercise, walking, sports, etc.</small>
                </div>

                <div class="checkin-section">
                    <label class="checkin-label" for="reflection_note">Personal Reflection (optional)</label>
                    <textarea id="reflection_note" name="reflection_note" class="checkin-textarea" rows="3"
                              placeholder="How are you feeling? Any thoughts? (Private - only you can see this)" maxlength="500"></textarea>
                    <small class="checkin-hint">This note is private and only visible to you.</small>
                </div>

                <button type="submit" class="btn-checkin">Complete Check-In ✓</button>
                <p class="checkin-time-hint">⏱ Takes less than 30 seconds</p>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
document.getElementById('checkinForm')?.addEventListener('submit', function() {
    const btn = this.querySelector('.btn-checkin');
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Saving...';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
