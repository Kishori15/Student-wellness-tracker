<?php
/**
 * Login page - username & password, session-based auth, redirect by role
 */
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../frontend/index.php');
    exit;
}

require_once 'config/db_connect.php';

$error = '';
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
        $selected_role = $_POST['role'] ?? '';

        if ($username === '' || $password === '' || $selected_role === '') {
            $error = 'Please enter username, password, and select a role.';
        } elseif (!in_array($selected_role, ['admin', 'student'], true)) {
            $error = 'Invalid role selected.';
        } else {
            $stmt = mysqli_prepare($conn, 'SELECT id, username, password, role FROM users WHERE username = ?');
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($user && password_verify($password, $user['password'])) {
                if ($user['role'] !== $selected_role) {
                    $error = 'Invalid role selected.';
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    if ($user['role'] === 'admin') {
                        header('Location: ../frontend/admin_dashboard.php');
                    } else {
                        header('Location: ../frontend/dashboard.php');
                    }
                    exit;
                }
            } else {
                $error = 'Invalid username or password!';
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
    <title>Login - Student Wellness Tracking Dashboard</title>
    <link rel="stylesheet" href="../frontend/assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <h1>Student Wellness Tracking Dashboard</h1>
        </div>

        <div class="login-card">
            <form method="post" action="login.php">
                <div class="form-group">
                    <label for="username">Email / Username:</label>
                    <input type="text" id="username" name="username" required
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="">Select role</option>
                        <option value="student" <?php echo (($_POST['role'] ?? '') === 'student') ? 'selected' : ''; ?>>Student</option>
                        <option value="admin" <?php echo (($_POST['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
                <?php if ($db_error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($db_error); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
            </form>
            <div style="margin-top:20px; padding-top:15px; border-top:1px solid #ddd; text-align:center;">
                <p style="font-size:14px; color:#666; margin-bottom:10px;">
                    Don't have an account?
                </p>
                <a href="register.php" style="display:inline-block; padding:10px 20px; background-color:#4caf50; color:white; text-decoration:none; border-radius:4px; font-weight:500;">
                    Register as Student
                </a>
            </div>
        </div>
    </div>
</body>
</html>
