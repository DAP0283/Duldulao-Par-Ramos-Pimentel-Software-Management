<?php
/**
 * Admin - View All Applications
 */
session_start();

// Validate admin session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/admin-login.php');
    exit();
}

// Get admin information from session
$admin_name = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Administrator';
$status_filter = $_GET['status'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Applications - Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Admin Panel</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="users.php">Manage Users</a></li>
                    <li><a href="applications.php" class="active">All Applications</a></li>
                    <li><a href="staff-management.php">Staff Management</a></li>
                    <li><a href="settings.php">System Settings</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>All Applications</h2>
                    <a href="dashboard.php" class="btn btn-sm btn-secondary">Back to Dashboard</a>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="admin-controls">
                    <select id="filter-status" class="form-control">
                        <option value="">All Applications</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in-progress" <?php echo $status_filter === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Application ID</th>
                            <th>Applicant Name</th>
                            <th>Service Type</th>
                            <th>Date Applied</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#APP-001</td>
                            <td>Juan Dela Cruz</td>
                            <td>Barangay Clearance</td>
                            <td>2026-03-05</td>
                            <td><span class="badge badge-success">Approved</span></td>
                            <td>Secretary</td>
                            <td><a href="#" class="btn btn-xs btn-info">View</a> <a href="#" class="btn btn-xs btn-danger">Delete</a></td>
                        </tr>
                        <tr>
                            <td>#APP-002</td>
                            <td>Maria Santos</td>
                            <td>Barangay ID</td>
                            <td>2026-03-04</td>
                            <td><span class="badge badge-warning">Pending</span></td>
                            <td>Unassigned</td>
                            <td><a href="#" class="btn btn-xs btn-info">View</a> <a href="#" class="btn btn-xs btn-warning">Assign</a> <a href="#" class="btn btn-xs btn-danger">Delete</a></td>
                        </tr>
                        <tr>
                            <td>#APP-003</td>
                            <td>Carlos Rodriguez</td>
                            <td>Burial Assistance</td>
                            <td>2026-03-01</td>
                            <td><span class="badge badge-info">In Progress</span></td>
                            <td>Treasurer</td>
                            <td><a href="#" class="btn btn-xs btn-info">View</a> <a href="#" class="btn btn-xs btn-danger">Delete</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
