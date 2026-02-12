<?php
/**
 * Shared header - shows top bar and sidebar based on role
 * Expects: $page_title (optional), $current_page (for active nav)
 */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../backend/login.php');
    exit;
}
$username = htmlspecialchars($_SESSION['username'] ?? 'User');
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Student Wellness</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="app-body">
    <!-- Top header bar -->
    <header class="top-header">
        <div class="header-left">Student Wellness</div>
        <div class="header-right">
            <span class="welcome-text">Welcome, <?php echo $role === 'admin' ? 'Admin' : $username; ?></span>
            <a href="../backend/logout.php" class="btn-logout">Logout &rarr;</a>
        </div>
    </header>

    <div class="app-layout">
        <!-- Left sidebar -->
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <?php if ($role === 'student'): ?>
                    <a href="dashboard.php" class="nav-item <?php echo ($current_page ?? '') === 'dashboard' ? 'active' : ''; ?>">
                        <span class="nav-icon">&#128202;</span> Dashboard
                    </a>
                    <a href="add_wellness.php" class="nav-item <?php echo ($current_page ?? '') === 'add_wellness' ? 'active' : ''; ?>">
                        <span class="nav-icon">&#128221;</span> Add Wellness Data
                    </a>
                    <a href="view_wellness.php" class="nav-item <?php echo ($current_page ?? '') === 'view_wellness' ? 'active' : ''; ?>">
                        <span class="nav-icon">&#128196;</span> View Reports
                    </a>
                <?php else: ?>
                    <a href="admin_dashboard.php" class="nav-item <?php echo ($current_page ?? '') === 'admin_dashboard' ? 'active' : ''; ?>">
                        <span class="nav-icon">&#128202;</span> Dashboard
                    </a>
                    <a href="admin_dashboard.php#records" class="nav-item <?php echo ($current_page ?? '') === 'records' ? 'active' : ''; ?>">
                        <span class="nav-icon">&#128101;</span> Student Records
                    </a>
                    <a href="reports.php" class="nav-item <?php echo ($current_page ?? '') === 'reports' ? 'active' : ''; ?>">
                        <span class="nav-icon">&#128196;</span> View Reports
                    </a>
                <?php endif; ?>
                <a href="../backend/logout.php" class="nav-item nav-logout">
                    <span class="nav-icon">&#128682;</span> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
