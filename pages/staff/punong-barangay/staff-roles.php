<?php
/**
 * Roles & Permissions Management - Punong Barangay
 * Configure roles and their permissions
 */
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: ../../../auth/staff-login.php');
    exit();
}

$staff_role = $_SESSION['role'] ?? '';
if ($staff_role !== 'Punong Barangay') {
    header('Location: ../dashboard.php');
    exit();
}

require_once('../../../includes/db_config.php');
require_once('../../../includes/auth_functions.php');

$staff_name = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles & Permissions - Barangay e-Services</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Barangay Executive</h3>
                <p class="role-badge">Punong Barangay</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="staff-management.php">Staff Management</a></li>
                    <li><a href="staff-roles.php" class="active">Roles & Permissions</a></li>
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Roles & Permissions Management</h2>
                    <div class="user-info">
                        <a href="../../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <section class="staff-section">
                    <h3>Manage Roles</h3>
                    <p>Configure roles and permissions for staff members.</p>
                    
                    <div style="margin-top: 20px;">
                        <h4>Available Roles</h4>
                        <table class="staff-table">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Punong Barangay</td>
                                    <td>Administrative head with full control</td>
                                    <td><button class="btn btn-xs btn-info">Configure</button></td>
                                </tr>
                                <tr>
                                    <td>Barangay Secretary</td>
                                    <td>Manages applications and client records</td>
                                    <td><button class="btn btn-xs btn-info">Configure</button></td>
                                </tr>
                                <tr>
                                    <td>Barangay Treasurer</td>
                                    <td>Manages financial transactions and reports</td>
                                    <td><button class="btn btn-xs btn-info">Configure</button></td>
                                </tr>
                                <tr>
                                    <td>Sanggunian Member</td>
                                    <td>Oversight and review permissions</td>
                                    <td><button class="btn btn-xs btn-info">Configure</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="../../../assets/js/main.js"></script>
</body>
</html>
