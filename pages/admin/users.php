<?php
/**
 * Admin - Manage Users
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
    <title>Manage Users - Admin Dashboard</title>
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
                    <li><a href="users.php" class="active">Manage Users</a></li>
                    <li><a href="applications.php">All Applications</a></li>
                    <li><a href="staff-management.php">Staff Management</a></li>
                    <li><a href="settings.php">System Settings</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Manage Users</h2>
                    <a href="dashboard.php" class="btn btn-sm btn-secondary">Back to Dashboard</a>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="admin-controls">
                    <input type="text" id="search-users" class="form-control" placeholder="Search users...">
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Date Registered</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#USER-001</td>
                            <td>Juan Dela Cruz</td>
                            <td>juan@example.com</td>
                            <td>2026-02-15</td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td><a href="#" class="btn btn-xs btn-info">View</a> <a href="#" class="btn btn-xs btn-warning">Edit</a> <a href="#" class="btn btn-xs btn-danger">Deactivate</a></td>
                        </tr>
                        <tr>
                            <td>#USER-002</td>
                            <td>Maria Santos</td>
                            <td>maria@example.com</td>
                            <td>2026-02-18</td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td><a href="#" class="btn btn-xs btn-info">View</a> <a href="#" class="btn btn-xs btn-warning">Edit</a> <a href="#" class="btn btn-xs btn-danger">Deactivate</a></td>
                        </tr>
                        <tr>
                            <td>#USER-003</td>
                            <td>Carlos Rodriguez</td>
                            <td>carlos@example.com</td>
                            <td>2026-02-20</td>
                            <td><span class="badge badge-danger">Inactive</span></td>
                            <td><a href="#" class="btn btn-xs btn-info">View</a> <a href="#" class="btn btn-xs btn-warning">Edit</a> <a href="#" class="btn btn-xs btn-success">Activate</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
