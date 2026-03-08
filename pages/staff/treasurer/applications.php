<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff' || $_SESSION['role'] !== 'Barangay Treasurer') {
    header('Location: ../../../auth/staff-login.php');
    exit();
}
require_once('../../../includes/db_config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications - Barangay e-Services</title>
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
                    <li><a href="applications.php" class="active">Applications</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Applications (View Only)</h2>
                    <a href="../../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                </div>
            </header>
            <div class="dashboard-content">
                <section class="staff-section">
                    <h3>All Applications</h3>
                    <p><em>View only access for application tracking</em></p>
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Applicant</th>
                                <th>Service</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5">No applications</td></tr>
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </div>
    <script src="../../../assets/js/main.js"></script>
</body>
</html>
