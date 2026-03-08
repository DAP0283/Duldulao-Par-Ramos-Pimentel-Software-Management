<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff' || $_SESSION['role'] !== 'Barangay Treasurer') {
    header('Location: ../../../auth/staff-login.php');
    exit();
}
require_once('../../../includes/db_config.php');
$staff_name = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Barangay e-Services</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Treasurer Portal</h3>
                <p class="role-badge">Barangay Treasurer</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="transactions.php">Transactions</a></li>
                    <li><a href="financial-reports.php">Financial Reports</a></li>
                    <li><a href="budget-management.php">Budget Management</a></li>
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="messages.php" class="active">Messages</a></li>
                    <li><a href="../../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Messages</h2>
                    <a href="../../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                </div>
            </header>
            <div class="dashboard-content">
                <section class="staff-section">
                    <h3>Messages & Communications</h3>
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th>From</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="4">No messages</td></tr>
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </div>
    <script src="../../../assets/js/main.js"></script>
</body>
</html>
