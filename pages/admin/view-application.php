<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/admin-login.php');
    exit();
}
require_once('../../includes/db_config.php');

$app_id = $_GET['id'] ?? null;
if (!$app_id) { header('Location: applications.php'); exit(); }

// Logic: Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $sql = "UPDATE Applications SET Status = ?, ProcessingNotes = ?, UpdatedAt = GETDATE(), ApprovedBy = ? WHERE ApplicationID = ?";
    $params = array($_POST['status'], $_POST['notes'], $_SESSION['user_id'], $app_id);
    sqlsrv_query($conn, $sql, $params);
    $msg = "Application updated successfully.";
}

// Fetch Full Details
$sql = "SELECT a.*, c.* FROM Applications a JOIN Clients c ON a.ClientID = c.ClientID WHERE a.ApplicationID = ?";
$stmt = sqlsrv_query($conn, $sql, array($app_id));
$app = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

$created_date = $app['CreatedAt'] instanceof DateTime ? $app['CreatedAt']->format('M d, Y | h:i A') : 'N/A'; //
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Application - Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><h3>Admin Panel</h3></div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="users.php">Manage Users</a></li>
                    <li><a href="applications.php" class="active">All Applications</a></li>
                    <li><a href="staff-management.php">Staff Management</a></li>
                    <li><a href="settings.php">System Settings</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Review Application: #APP-<?php echo str_pad($app_id, 3, '0', STR_PAD_LEFT); ?></h2>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if(isset($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>

                <div class="info-grid">
                    <div class="info-card">
                        <h3>Resident Profile</h3>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($app['FirstName'].' '.$app['MiddleName'].' '.$app['LastName']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($app['Email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($app['PhoneNumber'] ?? 'N/A'); ?></p>
                        <p><strong>Gender:</strong> <?php echo htmlspecialchars($app['Gender'] ?? 'N/A'); ?></p>
                        <p><strong>Birth Date:</strong> <?php echo ($app['BirthDate'] instanceof DateTime) ? $app['BirthDate']->format('Y-m-d') : 'N/A'; ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($app['Address'] ?? 'N/A'); ?></p>
                    </div>

                    <div class="info-card">
                        <h3>Request Details</h3>
                        <p><strong>Service Type:</strong> <?php echo htmlspecialchars($app['ServiceType']); ?></p>
                        <p><strong>Submission Date:</strong> <?php echo $created_date; ?></p>
                        <p><strong>Current Status:</strong> <span class="badge"><?php echo htmlspecialchars($app['Status']); ?></span></p>
                        <hr>
                        <p><strong>Raw Application Data:</strong></p>
                        <pre style="background: #f4f4f4; padding: 10px; font-size: 12px;"><?php echo htmlspecialchars($app['ApplicationData'] ?? 'No extra data provided.'); ?></pre>
                    </div>
                </div>

                <section class="admin-section" style="margin-top: 20px;">
                    <h3>Administrative Actions</h3>
                    <form method="POST" class="form-container">
                        <div class="form-group">
                            <label>Update Status</label>
                            <select name="status" class="form-control">
                                <?php foreach(['Pending', 'Processing', 'Approved', 'Rejected', 'Completed'] as $st) 
                                    echo "<option value='$st' ".($app['Status']==$st?'selected':'').">$st</option>"; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Processing Remarks (Visible to Staff)</label>
                            <textarea name="notes" class="form-control" rows="4"><?php echo htmlspecialchars($app['ProcessingNotes'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" name="update_status" class="btn btn-primary">Update Application</button>
                            <a href="applications.php" class="btn btn-secondary">Back to List</a>
                        </div>
                    </form>
                </section>
            </div>
        </main>
    </div>
</body>
</html>