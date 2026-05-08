<?php
session_start();
// Validate admin session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/admin-login.php');
    exit();
}

require_once('../../includes/db_config.php');
require_once('../../includes/news_functions.php');

// Fetch Philippine News
$news = getCachedPhilippineNews(5);
if (empty($news)) {
    $news = getFallbackNews(5);
}

// Fetch Live Statistics from Schema
$pending_count = 0;
$completed_count = 0;
$total_users = 0;

// Query for stats using schema tables
$res_p = sqlsrv_query($conn, "SELECT COUNT(*) as c FROM Applications WHERE Status = 'Pending'");
if($res_p) { $row = sqlsrv_fetch_array($res_p, SQLSRV_FETCH_ASSOC); $pending_count = $row['c']; }

$res_c = sqlsrv_query($conn, "SELECT COUNT(*) as c FROM Applications WHERE Status = 'Approved'");
if($res_c) { $row = sqlsrv_fetch_array($res_c, SQLSRV_FETCH_ASSOC); $completed_count = $row['c']; }

$res_u = sqlsrv_query($conn, "SELECT COUNT(*) as c FROM Clients WHERE IsActive = 1");
if($res_u) { $row = sqlsrv_fetch_array($res_u, SQLSRV_FETCH_ASSOC); $total_users = $row['c']; }

// Get 5 Most Recent Applications
$recent_query = "SELECT TOP 5 a.ApplicationID, c.FirstName, c.LastName, a.ServiceType, a.CreatedAt, a.Status 
                 FROM Applications a 
                 INNER JOIN Clients c ON a.ClientID = c.ClientID 
                 ORDER BY a.CreatedAt DESC";
$recent_apps = sqlsrv_query($conn, $recent_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Barangay e-Services</title>
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
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="users.php">Manage Users</a></li>
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
                    <h2>Admin Dashboard</h2>
                    </div>
            </header>

            <div class="dashboard-content">
                <div class="dashboard-content-main">
                <section class="stats-section">
                    <div class="stat-card stat-card-primary">
                        <div class="stat-icon">📋</div>
                        <h4>Pending Applications</h4>
                        <p class="stat-number"><?php echo $pending_count; ?></p>
                        <a href="applications.php?status=Pending" class="stat-link">View All Pending →</a>
                    </div>
                    <div class="stat-card stat-card-success">
                        <div class="stat-icon">✅</div>
                        <h4>Accepted Applications</h4>
                        <p class="stat-number"><?php echo $completed_count; ?></p>
                        <a href="applications.php?status=Approved" class="stat-link">View Accepted →</a>
                    </div>
                    <div class="stat-card stat-card-info">
                        <div class="stat-icon">👥</div>
                        <h4>Active Residents</h4>
                        <p class="stat-number"><?php echo $total_users; ?></p>
                        <a href="users.php" class="stat-link">Manage Users →</a>
                    </div>
                </section>

                <section class="admin-section">
                    <div class="section-header">
                        <h3>Recent Applications</h3>
                        <a href="applications.php" class="btn btn-sm btn-secondary">View All</a>
                    </div>
                    <table class="admin-table">
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
                            <?php 
                            if ($recent_apps) {
                                while ($row = sqlsrv_fetch_array($recent_apps, SQLSRV_FETCH_ASSOC)) {
                                    $applicant = $row['FirstName'] . ' ' . $row['LastName'];
                                    
                                    $badge_class = match($row['Status']) {
                                        'Approved', 'Completed' => 'badge-success',
                                        'Rejected' => 'badge-danger',
                                        'Processing' => 'badge-info',
                                        default => 'badge-warning'
                                    };

                                    // Correctly format the DateTime object to avoid previous TypeError
                                    $formatted_date = $row['CreatedAt'] instanceof DateTime ? $row['CreatedAt']->format('M d, Y') : 'N/A';
                            ?>
                            <tr>
                                <td class="text-bold">#APP-<?php echo str_pad($row['ApplicationID'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($applicant); ?></td>
                                <td><?php echo htmlspecialchars($row['ServiceType']); ?></td>
                                <td><?php echo $formatted_date; ?></td>
                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($row['Status']); ?></span></td>
                                <td>
                                    <a href="applications.php?id=<?php echo $row['ApplicationID']; ?>" class="btn btn-xs btn-info">Review</a>
                                </td>
                            </tr>
                            <?php } 
                            } else {
                                echo '<tr><td colspan="6" class="text-center">No recent applications found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </section>

                <section class="admin-section">
                    <h3>Quick Management</h3>
                    <div class="action-grid">
                        <a href="users.php" class="action-btn">
                            <span class="btn-icon">👤</span>
                            Manage Users
                        </a>
                        <a href="staff-management.php" class="action-btn">
                            <span class="btn-icon">🛡️</span>
                            Staff Directory
                        </a>
                        <a href="settings.php" class="action-btn">
                            <span class="btn-icon">⚙️</span>
                            System Settings
                        </a>
                    </div>
                </section>
                </div>

                <div class="dashboard-content-sidebar">
                    <?php echo displayNewsHTML($news, 5); ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>