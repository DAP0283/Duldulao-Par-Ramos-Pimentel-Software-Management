<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/admin-login.php');
    exit();
}
require_once('../../includes/db_config.php');

$success_message = '';

// Logic: Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        // Upsert logic for MSSQL SystemSettings table
        $sql = "IF EXISTS (SELECT 1 FROM SystemSettings WHERE SettingKey = ?)
                UPDATE SystemSettings SET SettingValue = ? WHERE SettingKey = ?
                ELSE
                INSERT INTO SystemSettings (SettingKey, SettingValue) VALUES (?, ?)";
        $params = array($key, $value, $key, $key, $value);
        sqlsrv_query($conn, $sql, $params);
    }
    $success_message = 'System configuration updated successfully.';
}

// Logic: Fetch all settings into an associative array
$settings = [];
$res = sqlsrv_query($conn, "SELECT SettingKey, SettingValue FROM SystemSettings");
if ($res) {
    while($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
        $settings[$row['SettingKey']] = $row['SettingValue'];
    }
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
                </div>
            </header>

            <div class="dashboard-content">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>

                <div class="form-container">
                    <form method="POST" action="">
                        <section class="admin-section">
                            <h3>General Configuration</h3>
                            <div class="form-group">
                                <label for="barangay_name">Barangay Name</label>
                                <input type="text" id="barangay_name" name="barangay_name" 
                                       class="form-control" value="<?php echo htmlspecialchars($settings['barangay_name'] ?? 'Barangay Sample'); ?>">
                            </div>

                            <div class="form-group">
                                <label for="barangay_address">Official Address</label>
                                <input type="text" id="barangay_address" name="barangay_address" 
                                       class="form-control" value="<?php echo htmlspecialchars($settings['barangay_address'] ?? 'Sample Address, City, Province'); ?>">
                            </div>

                            <div class="form-group">
                                <label for="contact_number">Public Contact Number</label>
                                <input type="tel" id="contact_number" name="contact_number" 
                                       class="form-control" value="<?php echo htmlspecialchars($settings['contact_number'] ?? '(123) 456-7890'); ?>">
                            </div>
                        </section>

                        <section class="admin-section" style="margin-top: 30px;">
                            <h3>Processing Controls</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="max_pending_apps">Max Pending Apps per User</label>
                                    <input type="number" id="max_pending_apps" name="max_pending_apps" 
                                           class="form-control" value="<?php echo htmlspecialchars($settings['max_pending_apps'] ?? '5'); ?>">
                                </div>
                                <div class="form-group" style="display: flex; align-items: center; padding-top: 25px;">
                                    <input type="checkbox" id="enable_online_payment" name="enable_online_payment" value="1" 
                                        <?php echo (isset($settings['enable_online_payment']) && $settings['enable_online_payment'] == '1') ? 'checked' : ''; ?>>
                                    <label for="enable_online_payment" style="margin-left: 10px; margin-bottom: 0;">Enable Online Payment System</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="notification_email">System Notification Email</label>
                                <input type="email" id="notification_email" name="notification_email" 
                                       class="form-control" value="<?php echo htmlspecialchars($settings['notification_email'] ?? 'notifications@barangay.gov'); ?>">
                            </div>
                        </section>

                        <div class="form-actions" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                            <button type="submit" class="btn btn-primary">Save All Settings</button>
                            <a href="dashboard.php" class="btn btn-secondary">Discard Changes</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>