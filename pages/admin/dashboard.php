<?php
/**
 * Admin Dashboard
 * Separate from client and staff dashboards
 */
session_start();

// Validate admin session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/admin-login.php');
    exit();
}

// Include database functions
require_once('../../includes/db_config.php');
require_once('../../includes/auth_functions.php');

// Get admin information from session
$admin_name = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Administrator';

// Get statistics from database
$pending_applications = 0;
$completed_applications = 0;
$total_users = 0;

// Query pending applications
$pending_result = sqlsrv_query($conn, "SELECT COUNT(*) as Count FROM Applications WHERE Status = 'Pending'");
if ($pending_result !== false) {
    $row = sqlsrv_fetch_array($pending_result, SQLSRV_FETCH_ASSOC);
    $pending_applications = $row['Count'] ?? 0;
}

// Query completed applications
$completed_result = sqlsrv_query($conn, "SELECT COUNT(*) as Count FROM Applications WHERE Status IN ('Approved', 'Completed')");
if ($completed_result !== false) {
    $row = sqlsrv_fetch_array($completed_result, SQLSRV_FETCH_ASSOC);
    $completed_applications = $row['Count'] ?? 0;
}

// Query total registered users
$users_result = sqlsrv_query($conn, "SELECT COUNT(*) as Count FROM Clients WHERE IsActive = 1");
if ($users_result !== false) {
    $row = sqlsrv_fetch_array($users_result, SQLSRV_FETCH_ASSOC);
    $total_users = $row['Count'] ?? 0;
}

// Get recent applications for display
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
    <title>Admin Dashboard - Barangay e-Services</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Admin Panel</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="users.php">Manage Users</a></li>
                    <li><a href="applications.php">All Applications</a></li>
                    <li><a href="staff-management.php">Staff Management</a></li>
                    <li><a href="settings.php">System Settings</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Admin Dashboard</h2>
                    <div class="user-info">
                        <a href="../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <!-- Statistics Cards -->
                <section class="stats-section">
                    <div class="stat-card stat-card-primary">
                        <h4>Pending Applications</h4>
                        <p class="stat-number"><?php echo $pending_applications; ?></p>
                        <a href="applications.php?status=pending">View Details</a>
                    </div>
                    <div class="stat-card stat-card-success">
                        <h4>Completed Applications</h4>
                        <p class="stat-number"><?php echo $completed_applications; ?></p>
                        <a href="applications.php?status=completed">View Details</a>
                    </div>
                    <div class="stat-card stat-card-info">
                        <h4>Total Registered Users</h4>
                        <p class="stat-number"><?php echo $total_users; ?></p>
                        <a href="users.php">Manage Users</a>
                    </div>
                </section>

                <!-- Recent Applications -->
                <section class="admin-section">
                    <h3>Recent Applications</h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Application ID</th>
                                <th>Applicant Name</th>
                                <th>Service Type</th>
                                <th>Date Applied</th>
                                <th>Status</th>
                                <th>Actions</th>
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
                                <td><?php echo date('Y-m-d', strtotime($row['CreatedAt'])); ?></td>
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

                <!-- Quick Actions -->
                <section class="admin-section">
                    <h3>Quick Actions</h3>
                    <div class="action-buttons">
                        <a href="users.php" class="btn btn-primary">Manage Users</a>
                        <a href="staff-management.php" class="btn btn-primary">Manage Staff</a>
                        <a href="settings.php" class="btn btn-secondary">System Settings</a>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
