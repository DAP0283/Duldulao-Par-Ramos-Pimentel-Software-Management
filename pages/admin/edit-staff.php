<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/admin-login.php');
    exit();
}
require_once('../../includes/db_config.php');

$staff_id = $_GET['id'] ?? null;
if (!$staff_id) { header('Location: staff-management.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logic: Update based on Position and Dept emphasis
    $sql = "UPDATE Staff SET FirstName = ?, LastName = ?, Email = ?, Position = ?, Role = ?, Department = ?, UpdatedAt = GETDATE() WHERE StaffID = ?";
    $params = array($_POST['fname'], $_POST['lname'], $_POST['email'], $_POST['position'], $_POST['role'], $_POST['dept'], $staff_id);
    
    if (sqlsrv_query($conn, $sql, $params)) {
        header("Location: staff-management.php?msg=Staff profile updated successfully.");
        exit();
    }
}

$res = sqlsrv_query($conn, "SELECT * FROM Staff WHERE StaffID = ?", array($staff_id));
$s = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Staff - Admin Dashboard</title>
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
                    <li><a href="applications.php">All Applications</a></li>
                    <li><a href="staff-management.php" class="active">Staff Management</a></li>
                    <li><a href="settings.php">System Settings</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Edit Staff Member: #STAFF-<?php echo str_pad($staff_id, 3, '0', STR_PAD_LEFT); ?></h2>
                </div>
            </header>

            <div class="dashboard-content">
                <form method="POST" class="form-container">
                    <div class="form-row">
                        <div class="form-group"><label>First Name</label><input type="text" name="fname" value="<?php echo htmlspecialchars($s['FirstName']); ?>" class="form-control"></div>
                        <div class="form-group"><label>Last Name</label><input type="text" name="lname" value="<?php echo htmlspecialchars($s['LastName']); ?>" class="form-control"></div>
                    </div>
                    <div class="form-group"><label>Email Address</label><input type="email" name="email" value="<?php echo htmlspecialchars($s['Email']); ?>" class="form-control"></div>
                    
                    <div class="form-row">
                        <div class="form-group"><label>Designated Position</label><input type="text" name="position" value="<?php echo htmlspecialchars($s['Position']); ?>" class="form-control"></div>
                        <div class="form-group">
                            <label>System Access Role</label>
                            <select name="role" class="form-control">
                                <?php 
                                $roles = ['Punong Barangay', 'Barangay Secretary', 'Barangay Treasurer', 'Sanggunian Member'];
                                foreach($roles as $role): ?>
                                    <option value="<?php echo $role; ?>" <?php echo ($s['Role'] == $role) ? 'selected' : ''; ?>><?php echo $role; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group"><label>Department / Office</label><input type="text" name="dept" value="<?php echo htmlspecialchars($s['Department']); ?>" class="form-control"></div>
                    
                    <div class="form-actions" style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">Save Staff Profile</button>
                        <a href="staff-management.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>