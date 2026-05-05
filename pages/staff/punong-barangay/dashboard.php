<?php
/**
 * Punong Barangay Dashboard
 * Administrative control over staff and applications
 */
session_start();

// Validate staff session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: ../../../auth/staff-login.php');
    exit();
}

// Verify user is Punong Barangay
$staff_role = $_SESSION['role'] ?? '';
if ($staff_role !== 'Punong Barangay') {
    header('Location: ../dashboard.php');
    exit();
}

// Include database functions
require_once('../../../includes/db_config.php');
require_once('../../../includes/auth_functions.php');

// Get staff information from session
$staff_id = $_SESSION['user_id'];
$staff_name = $_SESSION['name'];

// Get statistics
$total_staff = 0;
$total_applications = 0;
$pending_applications = 0;

// Query total staff
$staff_result = sqlsrv_query($conn, "SELECT COUNT(*) as Count FROM Staff WHERE IsActive = 1");
if ($staff_result !== false) {
    $row = sqlsrv_fetch_array($staff_result, SQLSRV_FETCH_ASSOC);
    $total_staff = $row['Count'] ?? 0;
}

// Query total applications
$apps_result = sqlsrv_query($conn, "SELECT COUNT(*) as Count FROM Applications");
if ($apps_result !== false) {
    $row = sqlsrv_fetch_array($apps_result, SQLSRV_FETCH_ASSOC);
    $total_applications = $row['Count'] ?? 0;
}

// Query pending applications
$pending_result = sqlsrv_query($conn, "SELECT COUNT(*) as Count FROM Applications WHERE Status = 'Pending'");
if ($pending_result !== false) {
    $row = sqlsrv_fetch_array($pending_result, SQLSRV_FETCH_ASSOC);
    $pending_applications = $row['Count'] ?? 0;
}

// Get recent applications
$recent_apps_query = "SELECT TOP 5 a.ApplicationID, c.FirstName, c.LastName, a.ServiceType, a.CreatedAt, a.Status 
                      FROM Applications a 
                      INNER JOIN Clients c ON a.ClientID = c.ClientID 
                      ORDER BY a.CreatedAt DESC";
$recent_apps = sqlsrv_query($conn, $recent_apps_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punong Barangay Dashboard - Barangay e-Services</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Barangay Executive</h3>
                <p class="role-badge">Punong Barangay</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="staff-management.php">Staff Management</a></li>
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Welcome, <?php echo htmlspecialchars($staff_name); ?></h2>
                </div>
            </header>

            <div class="dashboard-content">
                <!-- Stats Section -->
                <section class="stats-section">
                    <div class="stat-card stat-card-primary">
                        <h4>Active Staff Members</h4>
                        <p class="stat-number"><?php echo $total_staff; ?></p>
                        <a href="staff-management.php">Manage Staff</a>
                    </div>
                    <div class="stat-card stat-card-info">
                        <h4>Total Applications</h4>
                        <p class="stat-number"><?php echo $total_applications; ?></p>
                        <a href="applications.php">View All</a>
                    </div>
                    <div class="stat-card stat-card-warning">
                        <h4>Pending Applications</h4>
                        <p class="stat-number"><?php echo $pending_applications; ?></p>
                        <a href="applications.php?status=pending">Review</a>
                    </div>
                </section>

                <!-- Quick Actions -->
                <section class="staff-section">
                    <h3>Administrative Actions</h3>
                    <div class="action-buttons">
                        <a href="staff-management.php" class="btn btn-primary">Manage Staff</a>
                        <a href="applications.php" class="btn btn-secondary">Review Applications</a>
                        <a href="reports.php" class="btn btn-secondary">Generate Reports</a>
                    </div>
                </section>

                <!-- Recent Applications Overview -->
                <section class="staff-section">
                    <h3>Recent Applications</h3>
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th>Application ID</th>
                                <th>Applicant</th>
                                <th>Service Type</th>
                                <th>Date Applied</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($recent_apps !== false) {
                                $found = false;
                                while ($row = sqlsrv_fetch_array($recent_apps, SQLSRV_FETCH_ASSOC)) {
                                    $found = true;
                                    $applicant_name = $row['FirstName'] . ' ' . $row['LastName'];
                                    $status_badge = 'badge-warning';
                                    if ($row['Status'] === 'Approved' || $row['Status'] === 'Completed') {
                                        $status_badge = 'badge-success';
                                    } elseif ($row['Status'] === 'Rejected') {
                                        $status_badge = 'badge-danger';
                                    } elseif ($row['Status'] === 'In Progress') {
                                        $status_badge = 'badge-info';
                                    }
                            ?>
                            <tr>
                                <td>#APP-<?php echo str_pad($row['ApplicationID'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($applicant_name); ?></td>
                                <td><?php echo htmlspecialchars($row['ServiceType']); ?></td>
                                <td><?php echo ($row['CreatedAt'] instanceof DateTime) ? $row['CreatedAt']->format('Y-m-d') : date('Y-m-d', strtotime($row['CreatedAt'])); ?></td>
                                <td><span class="badge <?php echo $status_badge; ?>"><?php echo htmlspecialchars($row['Status']); ?></span></td>
                                <td><a href="applications.php?id=<?php echo $row['ApplicationID']; ?>" class="btn btn-xs btn-info">Review</a></td>
                            </tr>
                            <?php }
                                if (!$found) {
                                    echo '<tr><td colspan="6">No applications found</td></tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6">Unable to load applications</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                    <p><a href="applications.php">View all applications →</a></p>
                </section>
            </div>
        </main>
    </div>

    <script src="../../../assets/js/main.js"></script>
</body>
</html>
