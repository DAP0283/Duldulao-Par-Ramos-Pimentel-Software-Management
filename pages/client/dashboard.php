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
require_once('../../includes/news_functions.php');

// Fetch Philippine News
$news = getCachedPhilippineNews(5);
if (empty($news)) {
    $news = getFallbackNews(5);
}

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
    <title>Resident Dashboard - Barangay e-Services</title>
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
                    <h2>Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
                    <div class="user-info">
                        <a href="../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>

            <!-- Philippine News Section -->
            <div class="news-wrapper">
                <?php echo displayNewsHTML($news, 5); ?>
            </div>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <div class="dashboard-content-main">
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
                                $count++;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars(isset($app['id']) ? $app['id'] : '#APP-0'); ?></td>
                                <td><?php echo htmlspecialchars(isset($app['service']) ? $app['service'] : 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(isset($app['date']) ? $app['date'] : 'N/A'); ?></td>
                                <td><span class="badge <?php echo isset($app['status_class']) ? $app['status_class'] : 'badge-secondary'; ?>"><?php echo htmlspecialchars(isset($app['status']) ? $app['status'] : 'Pending'); ?></span></td>
                                <td><a href="view-application.php?id=<?php echo isset($app['application_id']) ? urlencode($app['application_id']) : '#'; ?>" class="btn btn-xs btn-info">View</a></td>
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

                <div class="dashboard-content-sidebar">
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
