<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff' || $_SESSION['role'] !== 'Barangay Secretary') {
    header('Location: ../../../auth/staff-login.php');
    exit();
}
require_once('../../../includes/db_config.php');
require_once('../../../includes/auth_functions.php');
$staff_name = $_SESSION['name'];
$pending_count = 0;
$in_progress_count = 0;
$completed_count = 0;
$pending_result = sqlsrv_query($conn, "SELECT COUNT(*) as Count FROM Applications WHERE Status = 'Pending'");
if ($pending_result !== false) {
    $row = sqlsrv_fetch_array($pending_result, SQLSRV_FETCH_ASSOC);
    $pending_count = $row['Count'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secretary Dashboard - Barangay e-Services</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Secretary Portal</h3>
                <p class="role-badge">Barangay Secretary</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="clients.php">Clients</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Welcome, <?php echo htmlspecialchars($staff_name); ?></h2>
                    <a href="../../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                </div>
            </header>
            <div class="dashboard-content">
                <section class="stats-section">
                    <div class="stat-card stat-card-primary">
                        <h4>Pending Applications</h4>
                        <p class="stat-number"><?php echo $pending_count; ?></p>
                        <a href="applications.php?status=pending">Review</a>
                    </div>
                    <div class="stat-card stat-card-info">
                        <h4>Client Records</h4>
                        <p class="stat-number">-</p>
                        <a href="clients.php">Manage</a>
                    </div>
                    <div class="stat-card stat-card-success">
                        <h4>Messages</h4>
                        <p class="stat-number">-</p>
                        <a href="messages.php">View</a>
                    </div>
                </section>
                <section class="staff-section">
                    <h3>Quick Actions</h3>
                    <div class="action-buttons">
                        <a href="applications.php" class="btn btn-primary">Review Applications</a>
                        <a href="clients.php" class="btn btn-primary">Manage Clients</a>
                        <a href="messages.php" class="btn btn-secondary">Messages</a>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <script src="../../../assets/js/main.js"></script>
</body>
</html>
