<?php
/**
 * Reports - Punong Barangay
 * Administrative and performance reports
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
    <title>Reports - Barangay e-Services</title>
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
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="reports.php" class="active">Reports</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Administrative Reports</h2>
                </div>
            </header>

            <div class="dashboard-content">
                <section class="staff-section">
                    <h3>Available Reports</h3>
                    <div class="action-buttons">
                        <a href="#" class="btn btn-primary">Applications Report</a>
                        <a href="#" class="btn btn-primary">Staff Performance</a>
                        <a href="#" class="btn btn-primary">Financial Summary</a>
                        <a href="#" class="btn btn-primary">Monthly Statistics</a>
                    </div>
                </section>

                <section class="staff-section">
                    <h3>Recent Reports</h3>
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th>Report Type</th>
                                <th>Generated</th>
                                <th>By</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Monthly Statistics</td>
                                <td>2026-03-05</td>
                                <td>System</td>
                                <td><a href="#" class="btn btn-xs btn-info">View</a></td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </div>

    <script src="../../../assets/js/main.js"></script>
</body>
</html>
