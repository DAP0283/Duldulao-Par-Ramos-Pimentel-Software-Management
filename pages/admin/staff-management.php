<?php
/**
 * Admin - Staff Management
 * Manage Sangguniang Barangay, Secretary, Treasurer, and Punong Barangay accounts
 */
session_start();

// Validate admin session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/admin-login.php');
    exit();
}

// Get admin information from session
$admin_name = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - Admin Dashboard</title>
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
                    <li><a href="applications.php">All Applications</a></li>
                    <li><a href="staff-management.php" class="active">Staff Management</a></li>
                    <li><a href="settings.php">System Settings</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Staff Management</h2>
                    <a href="add-staff.php" class="btn btn-primary">Add New Staff</a>
                </div>
            </header>

            <div class="dashboard-content">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Staff ID</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#STAFF-001</td>
                            <td>Punong Barangay</td>
                            <td>Barangay Captain</td>
                            <td>punong@barangay.gov</td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td><a href="#" class="btn btn-xs btn-info">View</a> <a href="#" class="btn btn-xs btn-warning">Edit</a> <a href="#" class="btn btn-xs btn-danger">Deactivate</a></td>
                        </tr>
                        <tr>
                            <td>#STAFF-002</td>
                            <td>Barangay Secretary</td>
                            <td>Secretary</td>
                            <td>secretary@barangay.gov</td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td><a href="#" class="btn btn-xs btn-info">View</a> <a href="#" class="btn btn-xs btn-warning">Edit</a> <a href="#" class="btn btn-xs btn-danger">Deactivate</a></td>
                        </tr>
                        <tr>
                            <td>#STAFF-003</td>
                            <td>Barangay Treasurer</td>
                            <td>Treasurer</td>
                            <td>treasurer@barangay.gov</td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td><a href="#" class="btn btn-xs btn-info">View</a> <a href="#" class="btn btn-xs btn-warning">Edit</a> <a href="#" class="btn btn-xs btn-danger">Deactivate</a></td>
                        </tr>
                        <tr>
                            <td>#STAFF-004</td>
                            <td>Sangguniang Barangay Member 1</td>
                            <td>Sangguniang Member</td>
                            <td>member1@barangay.gov</td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td><a href="#" class="btn btn-xs btn-info">View</a> <a href="#" class="btn btn-xs btn-warning">Edit</a> <a href="#" class="btn btn-xs btn-danger">Deactivate</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
