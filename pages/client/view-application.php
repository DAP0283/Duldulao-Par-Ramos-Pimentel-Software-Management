<?php
/**
 * Client - View Application Details
 */
session_start();

// Validate client session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../../auth/client-login.php');
    exit();
}

// Get user information from session
$user_name = $_SESSION['name'];

$app_id = $_GET['id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application - Barangay e-Services</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
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

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Application Details</h2>
                    <a href="my-applications.php" class="btn btn-sm btn-secondary">Back to Applications</a>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="form-container">
                    <section class="application-details">
                        <h3>Application <?php echo htmlspecialchars($app_id); ?></h3>

                        <div class="detail-row">
                            <label>Application ID:</label>
                            <span><?php echo htmlspecialchars($app_id); ?></span>
                        </div>

                        <div class="detail-row">
                            <label>Service Type:</label>
                            <span>Barangay Clearance</span>
                        </div>

                        <div class="detail-row">
                            <label>Date Applied:</label>
                            <span>2026-03-05</span>
                        </div>

                        <div class="detail-row">
                            <label>Current Status:</label>
                            <span><span class="badge badge-success">Approved</span></span>
                        </div>

                        <div class="detail-row">
                            <label>Approval Date:</label>
                            <span>2026-03-06</span>
                        </div>

                        <div class="detail-row">
                            <label>Approved By:</label>
                            <span>Barangay Secretary</span>
                        </div>

                        <h3 style="margin-top: 2rem;">Application Details</h3>

                        <div class="detail-row">
                            <label>Full Name:</label>
                            <span>Juan Dela Cruz</span>
                        </div>

                        <div class="detail-row">
                            <label>Date of Birth:</label>
                            <span>January 15, 1990</span>
                        </div>

                        <div class="detail-row">
                            <label>Address:</label>
                            <span>123 Sample Street, Barangay, City</span>
                        </div>

                        <div class="detail-row">
                            <label>Contact Number:</label>
                            <span>(123) 456-7890</span>
                        </div>

                        <div class="detail-row">
                            <label>Purpose:</label>
                            <span>Employment</span>
                        </div>

                        <h3 style="margin-top: 2rem;">Processing Notes</h3>

                        <div class="announcement-box">
                            <p>Your application has been approved. You may pick up your clearance at the barangay office during business hours.</p>
                        </div>
                    </section>

                    <div class="form-actions">
                        <button type="button" class="btn btn-primary" onclick="printDocument()">Print</button>
                        <a href="my-applications.php" class="btn btn-secondary">Back to Applications</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
