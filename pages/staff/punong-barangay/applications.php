<?php
/**
 * Applications Management - Punong Barangay
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
$status_filter = $_GET['status'] ?? '';
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
                <h3>Barangay Executive</h3>
                <p class="role-badge">Punong Barangay</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="staff-management.php">Staff Management</a></li>
                    <li><a href="staff-roles.php">Roles & Permissions</a></li>
                    <li><a href="applications.php" class="active">Applications</a></li>
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Applications</h2>
                    <a href="dashboard.php" class="btn btn-sm btn-secondary">Back to Dashboard</a>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="admin-controls">
                    <select id="filter-status" class="form-control">
                        <option value="">All Applications</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending Review</option>
                        <option value="in-progress" <?php echo $status_filter === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    <input type="text" id="search-app" class="form-control" placeholder="Search applicant name...">
                </div>

                <section class="staff-section">
                    <h3>All Applications</h3>
                    <table class="staff-table">
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
                            <tr>
                                <td>#APP-001</td>
                                <td>Juan dela Cruz</td>
                                <td>Barangay Clearance</td>
                                <td>2026-03-05</td>
                                <td><span class="badge badge-warning">Pending</span></td>
                                <td><a href="#" class="btn btn-xs btn-info">Review</a></td>
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
