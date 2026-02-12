<?php
/**
 * Student Registration - create new student account.
 * Only creates role = 'student'. Passwords are hashed.
 */
session_start();

// If already logged in, send to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ../frontend/index.php');
    exit;
}

require_once 'config/db_connect.php';

$error = '';
$success = '';
$db_error = '';
if (!$conn) {
    $db_error = 'Database connection failed. Please ensure MySQL is running and database "student_wellness_db" exists.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$conn) {
        $error = 'Database not available. Please check MySQL server.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        // Basic validation
        if ($username === '' || $password === '' || $confirm === '') {
            $error = 'Please fill in all fields.';
        } elseif (strlen($username) < 3) {
            $error = 'Username must be at least 3 characters.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            // Check if username already exists
            $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE username = ?');
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $exists = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($exists) {
                $error = 'Username is already taken. Please choose another.';
            } else {
                // Insert new student user
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $role = 'student';
                $stmt = mysqli_prepare($conn, 'INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
                mysqli_stmt_bind_param($stmt, 'sss', $username, $hash, $role);

                if (mysqli_stmt_execute($stmt)) {
                    $success = 'Account created successfully. You can now login.';
                } else {
                    $error = 'Could not create account. Please try again.';
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Student Wellness Tracking Dashboard</title>
    <link rel="stylesheet" href="../frontend/assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <h1>Student Wellness Tracking Dashboard</h1>
        </div>

        <div class="login-card">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-group">
                    <label for="username">Choose Username:</label>
                    <input type="text" id="username" name="username" required
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Create Account</button>

                <?php if ($db_error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($db_error); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                        <br>
                        <a href="login.php">Go to Login</a>
                    </div>
                <?php endif; ?>
            </form>
            <p style="margin-top:15px; font-size:14px;">
                Already have an account?
                <a href="login.php">Login here</a>.
            </p>
        </div>
    </div>
</body>
</html>

