<?php
/**
 * Staff Dashboard (Unified Role-Based Routing)
 */
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: ../../auth/staff-login.php');
    exit();
}

require_once('../../includes/db_config.php');
require_once('../../includes/auth_functions.php');

$staff_id = $_SESSION['user_id'];
$staff_name = $_SESSION['name'];
$staff_role = trim($_SESSION['role'] ?? 'Staff');

// Standardized Roles: Punong Barangay, Barangay Secretary, Barangay Treasurer, Sanggunian Member
if ($staff_role === 'Barangay Treasurer') {
    header('Location: treasurer/dashboard.php');
    exit();
} elseif ($staff_role === 'Punong Barangay') {
    header('Location: punong-barangay/dashboard.php');
    exit();
} elseif ($staff_role === 'Barangay Secretary') {
    header('Location: secretary/dashboard.php');
    exit();
}

// Stats logic for Sanggunian Member
$pending_count = 0;
$in_progress_count = 0;
$completed_count = 0;

$p_res = sqlsrv_query($conn, "SELECT COUNT(*) as Count FROM Applications WHERE Status = 'Pending'");
$pending_count = ($p_res) ? sqlsrv_fetch_array($p_res, SQLSRV_FETCH_ASSOC)['Count'] : 0;

$ip_res = sqlsrv_query($conn, "SELECT COUNT(*) as Count FROM Applications WHERE Status = 'In Progress'");
$in_progress_count = ($ip_res) ? sqlsrv_fetch_array($ip_res, SQLSRV_FETCH_ASSOC)['Count'] : 0;

$c_res = sqlsrv_query($conn, "SELECT COUNT(*) as Count FROM Applications WHERE Status IN ('Approved', 'Completed') AND MONTH(CreatedAt) = MONTH(GETDATE())");
$completed_count = ($c_res) ? sqlsrv_fetch_array($c_res, SQLSRV_FETCH_ASSOC)['Count'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Barangay e-Services</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Staff Portal</h3>
                <p class="role-badge"><?php echo htmlspecialchars($staff_role); ?></p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Dashboard</h2>
                    <div class="user-info">
                        <span>Welcome, <strong><?php echo htmlspecialchars($staff_name); ?></strong></span>
                        <a href="../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <section class="stats-section">
                    <div class="stat-card stat-card-primary">
                        <h4>Pending Applications</h4>
                        <p class="stat-number"><?php echo $pending_count; ?></p>
                        <a href="applications.php?status=pending">View All</a>
                    </div>
                    <div class="stat-card stat-card-warning">
                        <h4>In Progress</h4>
                        <p class="stat-number"><?php echo $in_progress_count; ?></p>
                    </div>
                    <div class="stat-card stat-card-success">
                        <h4>Monthly Completed</h4>
                        <p class="stat-number"><?php echo $completed_count; ?></p>
                    </div>
                </section>

                <section class="staff-section">
                    <h3>Applications Summary</h3>
                    <p>Overview of current barangay requests and legislative tasks. <a href="applications.php">Go to Applications List →</a></p>
                </section>
            </div>
        </main>
    </div>
</body>
</html>