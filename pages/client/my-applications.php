<?php
/**
 * Client - My Applications Page
 * Shows all submitted applications with status
 */
session_start();

// Include database functions
require_once('../../includes/db_config.php');
require_once('../../includes/auth_functions.php');

// Validate client session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../../auth/client-login.php');
    exit();
}

// Get user information from session
$user_name = $_SESSION['name'];

// Get real applications from database
$applications = getClientApplications($_SESSION['user_id']);

// If no applications, show empty message
if (empty($applications)) {
    $applications = array();
}

// Get client profile data for display
$profile = getClientProfile($_SESSION['user_id']);
if ($profile) {
    $client_name = $profile['FirstName'] . ' ' . $profile['LastName'];
} else {
    $client_name = $user_name;
}

// Check for successful submission redirect
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_GET['application_id'])) {
    $success_message = 'Application submitted successfully! Your Application ID is: ' . htmlspecialchars($_GET['application_id']) . '. You will receive updates via email.';
}
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
                    <li><a href="../../auth/2fa-setup.php">Security Settings</a></li>
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
                        <a href="../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>

            <!-- Applications List -->
            <div class="dashboard-content">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <strong>Success!</strong> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

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
