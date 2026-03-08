<?php
/**
 * Admin - System Settings
 */
session_start();

// Validate admin session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/admin-login.php');
    exit();
}

// Get admin information from session
$admin_name = $_SESSION['name'] ?? $_SESSION['username'] ?? 'Administrator';

$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: Save settings to database
    $success_message = 'Settings saved successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Admin Dashboard</title>
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
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="users.php">Manage Users</a></li>
                    <li><a href="applications.php">All Applications</a></li>
                    <li><a href="staff-management.php">Staff Management</a></li>
                    <li><a href="settings.php" class="active">System Settings</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>System Settings</h2>
                    <a href="dashboard.php" class="btn btn-sm btn-secondary">Back to Dashboard</a>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="form-container">
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <h3>Barangay Information</h3>

                        <div class="form-group">
                            <label for="barangay_name">Barangay Name</label>
                            <input type="text" id="barangay_name" name="barangay_name" 
                                   class="form-control" value="Barangay Sample">
                        </div>

                        <div class="form-group">
                            <label for="barangay_address">Address</label>
                            <input type="text" id="barangay_address" name="barangay_address" 
                                   class="form-control" value="Sample Address, City, Province">
                        </div>

                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="tel" id="contact_number" name="contact_number" 
                                   class="form-control" value="(123) 456-7890">
                        </div>

                        <h3 style="margin-top: 30px;">Application Processing Settings</h3>

                        <div class="form-group">
                            <label for="max_pending_apps">Maximum Pending Applications per User</label>
                            <input type="number" id="max_pending_apps" name="max_pending_apps" 
                                   class="form-control" value="5">
                        </div>

                        <div class="form-group">
                            <label for="enable_online_payment">Enable Online Payment</label>
                            <input type="checkbox" id="enable_online_payment" name="enable_online_payment" checked>
                        </div>

                        <h3 style="margin-top: 30px;">Email Notifications</h3>

                        <div class="form-group">
                            <label for="notification_email">Notification Email</label>
                            <input type="email" id="notification_email" name="notification_email" 
                                   class="form-control" value="notifications@barangay.gov">
                        </div>

                        <div class="form-group">
                            <label for="enable_notifications">Enable Email Notifications</label>
                            <input type="checkbox" id="enable_notifications" name="enable_notifications" checked>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save Settings</button>
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
