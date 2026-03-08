<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff' || $_SESSION['role'] !== 'Barangay Secretary') {
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
    <title>Clients - Barangay e-Services</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Secretary Portal</h3>
                <p class="role-badge">Barangay Secretary</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="clients.php" class="active">Clients</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Client Management</h2>
                    <a href="dashboard.php" class="btn btn-sm btn-secondary">Back</a>
                </div>
            </header>
            <div class="dashboard-content">
                <section class="staff-section">
                    <h3>Client Records</h3>
                    <input type="text" class="form-control" placeholder="Search clients..." style="margin-bottom: 15px;">
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Joined</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5">No clients found</td></tr>
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </div>
    <script src="../../../assets/js/main.js"></script>
</body>
</html>
