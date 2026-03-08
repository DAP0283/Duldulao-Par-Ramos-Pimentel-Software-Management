<?php
/**
 * Staff Dashboard (Role-Based)
 * Used by: Sangguniang Barangay, Barangay Secretary, Barangay Treasurer, Punong Barangay
 * Access level determined by user role
 */
session_start();

// Validate staff session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: ../../auth/staff-login.php');
    exit();
}

// Include database functions
require_once('../../includes/db_config.php');
require_once('../../includes/auth_functions.php');

// Get staff information from session
$staff_id = $_SESSION['user_id'];
$staff_name = $_SESSION['name'];
$staff_role = $_SESSION['role'] ?? 'Staff';
$staff_position = $_SESSION['position'] ?? '';

// Role-based dashboard routing
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

// Determine permissions based on role
$permissions = [
    'view_applications' => true,
    'approve_applications' => in_array($staff_role, ['Barangay Secretary', 'Punong Barangay', 'Barangay Treasurer']),
    'edit_records' => in_array($staff_role, ['Barangay Secretary']),
    'view_budget' => in_array($staff_role, ['Barangay Treasurer', 'Punong Barangay']),
    'send_messages' => true,
];

// Get statistics for staff
$pending_count = 0;
$in_progress_count = 0;
$completed_count = 0;

// Query pending applications
$pending_result = sqlsrv_query($conn, "SELECT COUNT(*) as Count FROM Applications WHERE Status = 'Pending'");
if ($pending_result !== false) {
    $row = sqlsrv_fetch_array($pending_result, SQLSRV_FETCH_ASSOC);
    $pending_count = $row['Count'] ?? 0;
}

// Query in-progress applications
$progress_result = sqlsrv_query($conn, "SELECT COUNT(*) as Count FROM Applications WHERE Status = 'In Progress'");
if ($progress_result !== false) {
    $row = sqlsrv_fetch_array($progress_result, SQLSRV_FETCH_ASSOC);
    $in_progress_count = $row['Count'] ?? 0;
}

// Query completed this month
$completed_result = sqlsrv_query($conn, "SELECT COUNT(*) as Count FROM Applications WHERE Status IN ('Approved', 'Completed') AND MONTH(UpdatedAt) = MONTH(GETDATE()) AND YEAR(UpdatedAt) = YEAR(GETDATE())");
if ($completed_result !== false) {
    $row = sqlsrv_fetch_array($completed_result, SQLSRV_FETCH_ASSOC);
    $completed_count = $row['Count'] ?? 0;
}

// Get pending applications for review
$pending_apps_query = "SELECT TOP 5 a.ApplicationID, c.FirstName, c.LastName, a.ServiceType, a.CreatedAt, a.Status 
                       FROM Applications a 
                       INNER JOIN Clients c ON a.ClientID = c.ClientID 
                       WHERE a.Status = 'Pending' 
                       ORDER BY a.CreatedAt ASC";
$pending_apps = sqlsrv_query($conn, $pending_apps_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Barangay e-Services</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Staff Portal</h3>
                <p class="role-badge"><?php echo htmlspecialchars($staff_role); ?></p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <?php if (in_array($staff_role, ['Barangay Secretary', 'Barangay Treasurer', 'Punong Barangay'])): ?>
                    <li><a href="applications.php">Applications</a></li>
                    <?php endif; ?>
                    <?php if (in_array($staff_role, ['Barangay Secretary', 'Punong Barangay'])): ?>
                    <li><a href="clients.php">Clients</a></li>
                    <?php endif; ?>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Welcome, <?php echo htmlspecialchars($staff_name); ?></h2>
                    <div class="user-info">
                        <a href="../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <!-- Stats Section -->
                <section class="stats-section">
                    <div class="stat-card stat-card-primary">
                        <h4>Pending Applications</h4>
                        <p class="stat-number"><?php echo $pending_count; ?></p>
                        <a href="applications.php?status=pending">View</a>
                    </div>
                    <div class="stat-card stat-card-info">
                        <h4>In Progress</h4>
                        <p class="stat-number"><?php echo $in_progress_count; ?></p>
                        <a href="applications.php?status=in-progress">View</a>
                    </div>
                    <div class="stat-card stat-card-success">
                        <h4>Completed This Month</h4>
                        <p class="stat-number"><?php echo $completed_count; ?></p>
                        <a href="applications.php?status=completed">View</a>
                    </div>
                </section>

                <!-- Quick Actions -->
                <section class="staff-section">
                    <h3>Quick Actions</h3>
                    <div class="action-buttons">
                        <a href="applications.php" class="btn btn-primary">Review Applications</a>
                        <a href="clients.php" class="btn btn-primary">View Clients</a>
                        <a href="messages.php" class="btn btn-secondary">Messages</a>
                    </div>
                </section>

                <!-- Applications Needing Attention -->
                <section class="staff-section">
                    <h3>Applications Pending Review</h3>
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
                            if ($pending_apps !== false) {
                                $found = false;
                                while ($row = sqlsrv_fetch_array($pending_apps, SQLSRV_FETCH_ASSOC)) {
                                    $found = true;
                                    $applicant_name = $row['FirstName'] . ' ' . $row['LastName'];
                            ?>
                            <tr>
                                <td>#APP-<?php echo str_pad($row['ApplicationID'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($applicant_name); ?></td>
                                <td><?php echo htmlspecialchars($row['ServiceType']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($row['CreatedAt'])); ?></td>
                                <td><span class="badge badge-warning">Pending Review</span></td>
                                <td><a href="review-application.php?id=<?php echo $row['ApplicationID']; ?>" class="btn btn-xs btn-info">Review</a></td>
                            </tr>
                            <?php }
                                if (!$found) {
                                    echo '<tr><td colspan="6">No applications pending review</td></tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6">Unable to load applications</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                    <p><a href="applications.php">View all applications →</a></p>
                </section>

                <!-- Announcements -->
                <section class="staff-section">
                    <h3>Recent Announcements</h3>
                    <div class="announcement-box">
                        <h4>System Maintenance</h4>
                        <p>System maintenance scheduled for March 10, 2026, 10:00 PM - 12:00 AM. The system will be temporarily unavailable.</p>
                        <p class="text-muted">Posted on March 7, 2026</p>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
