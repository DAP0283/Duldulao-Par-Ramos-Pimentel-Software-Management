<?php
/**
 * Client Dashboard
 * Displays applications and allows service submissions
 */
session_start();

// Validate client session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../../auth/client-login.php');
    exit();
}

// Include database functions
require_once('../../includes/db_config.php');
require_once('../../includes/auth_functions.php');

// Get user information from session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// Get client profile data
$profile = getClientProfile($user_id);
if ($profile) {
    $client_name = $profile['FirstName'] . ' ' . $profile['LastName'];
} else {
    $client_name = $user_name;
}

// Get client's recent applications
$recent_apps = getClientApplications($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - Barangay e-Services</title>
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
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="my-applications.php">My Applications</a></li>
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
                    <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
                    <div class="user-info">
                        <a href="../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <section class="welcome-section">
                    <h3>Apply for Services</h3>
                    <p>Choose a service below to submit your application</p>
                </section>

                <div class="service-applications">
                    <!-- Barangay ID Application -->
                    <div class="app-card">
                        <h4>Request for a Barangay ID</h4>
                        <p>Get your official Barangay ID for identification and benefits verification.</p>
                        <p class="service-fee"><strong>Processing Time:</strong> 3-5 business days</p>
                        <a href="apply-barangay-id.php" class="btn btn-primary">Apply Now</a>
                        <a href="my-applications.php" class="btn btn-secondary">View Applications</a>
                    </div>

                    <!-- Burial Assistance Application -->
                    <div class="app-card">
                        <h4>Burial Assistance</h4>
                        <p>Apply for financial assistance for burial and funeral services.</p>
                        <p class="service-fee"><strong>Processing Time:</strong> 2-3 business days</p>
                        <a href="apply-burial.php" class="btn btn-primary">Apply Now</a>
                        <a href="my-applications.php" class="btn btn-secondary">View Applications</a>
                    </div>

                    <!-- Barangay Clearance Application -->
                    <div class="app-card">
                        <h4>Barangay Clearance</h4>
                        <p>Obtain a clearance certificate for employment, travel, and other purposes.</p>
                        <p class="service-fee"><strong>Processing Time:</strong> 1-2 business days</p>
                        <a href="apply-clearance.php" class="btn btn-primary">Apply Now</a>
                        <a href="my-applications.php" class="btn btn-secondary">View Applications</a>
                    </div>

                    <!-- Complaint Report Application -->
                    <div class="app-card">
                        <h4>Complaint Report</h4>
                        <p>File a formal complaint against individuals or organizations.</p>
                        <p class="service-fee"><strong>Processing Time:</strong> 5-7 business days</p>
                        <a href="apply-complaint.php" class="btn btn-primary">File Complaint</a>
                        <a href="my-applications.php" class="btn btn-secondary">View Applications</a>
                    </div>
                </div>

                <!-- Recent Applications Section -->
                <section class="recent-section">
                    <h3>Recent Applications</h3>
                    <?php if ($recent_apps && is_array($recent_apps)): ?>
                    <table class="applications-table">
                        <thead>
                            <tr>
                                <th>Application ID</th>
                                <th>Service Type</th>
                                <th>Date Applied</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 0;
                            foreach ($recent_apps as $app): 
                                if ($count >= 5) break; // Show only 5 recent
                                $status_badge = 'badge-warning';
                                if ($app['Status'] === 'Approved' || $app['Status'] === 'Completed') {
                                    $status_badge = 'badge-success';
                                } elseif ($app['Status'] === 'Rejected') {
                                    $status_badge = 'badge-danger';
                                } elseif ($app['Status'] === 'In Progress') {
                                    $status_badge = 'badge-info';
                                }
                                $count++;
                            ?>
                            <tr>
                                <td>#APP-<?php echo str_pad($app['ApplicationID'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($app['ServiceType']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($app['CreatedAt'])); ?></td>
                                <td><span class="badge <?php echo $status_badge; ?>"><?php echo htmlspecialchars($app['Status']); ?></span></td>
                                <td><a href="view-application.php?id=<?php echo $app['ApplicationID']; ?>" class="btn btn-xs btn-info">View</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p>No applications yet. <a href="apply-barangay-id.php">Start by applying for a service</a></p>
                    <?php endif; ?>
                    <p><a href="my-applications.php">View all applications →</a></p>
                </section>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
