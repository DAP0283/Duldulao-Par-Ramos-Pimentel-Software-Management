<?php
/**
 * Client - My Applications Page
 * Shows all submitted applications with status
 */
session_start();

// Validate client session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../../auth/client-login.php');
    exit();
}

// Get user information from session
$user_name = $_SESSION['name'];

// Mock data - will be replaced with database query
$applications = [
    ['id' => '#APP-001', 'service' => 'Barangay Clearance', 'date' => '2026-03-05', 'status' => 'Approved', 'status_class' => 'badge-success'],
    ['id' => '#APP-002', 'service' => 'Barangay ID', 'date' => '2026-03-04', 'status' => 'Pending', 'status_class' => 'badge-warning'],
    ['id' => '#APP-003', 'service' => 'Burial Assistance', 'date' => '2026-03-01', 'status' => 'In Progress', 'status_class' => 'badge-info'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - Barangay e-Services</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Barangay e-Services</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="my-applications.php" class="active">My Applications</a></li>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation Bar -->
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>My Applications</h2>
                    <div class="user-info">
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                        <a href="../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>

            <!-- Applications List -->
            <div class="dashboard-content">
                <div class="filter-section">
                    <label for="filter-status">Filter by Status:</label>
                    <select id="filter-status" class="form-control">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <table class="applications-table">
                    <thead>
                        <tr>
                            <th>Application ID</th>
                            <th>Service Type</th>
                            <th>Date Applied</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($app['id']); ?></td>
                            <td><?php echo htmlspecialchars($app['service']); ?></td>
                            <td><?php echo htmlspecialchars($app['date']); ?></td>
                            <td><span class="badge <?php echo $app['status_class']; ?>"><?php echo htmlspecialchars($app['status']); ?></span></td>
                            <td>
                                <a href="view-application.php?id=<?php echo urlencode($app['id']); ?>" class="btn btn-xs btn-info">View Details</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <p class="text-muted">No more applications to display.</p>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
