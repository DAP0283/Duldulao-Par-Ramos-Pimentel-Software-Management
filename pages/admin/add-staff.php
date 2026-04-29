<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/admin-login.php');
    exit();
}
require_once('../../includes/db_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logic: Insert using schema columns
    $sql = "INSERT INTO Staff (FirstName, LastName, Email, Password, Position, Role, Department, IsActive, CreatedAt) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, GETDATE())";
    
    // Position and Department are prioritized in the form logic
    $params = array($_POST['fname'], $_POST['lname'], $_POST['email'], $_POST['password'], $_POST['position'], $_POST['role'], $_POST['dept']);
    
    if (sqlsrv_query($conn, $sql, $params)) {
        header("Location: staff-management.php?msg=Staff account successfully created.");
        exit();
    } else {
        $error = "Error creating account. Please check your inputs.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Staff - Admin Dashboard</title>
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
                    <h2>Add New Staff Member</h2>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                
                <form method="POST" class="form-container">
                    <div class="form-row">
                        <div class="form-group"><label>First Name</label><input type="text" name="fname" class="form-control" required></div>
                        <div class="form-group"><label>Last Name</label><input type="text" name="lname" class="form-control" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-control" required></div>
                        <div class="form-group"><label>Login Password</label><input type="password" name="password" class="form-control" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Designated Position</label>
                            <input type="text" name="position" class="form-control" placeholder="e.g. Barangay Treasurer" required>
                        </div>
                        <div class="form-group">
                            <label>System Access Role</label>
                            <select name="role" class="form-control">
                                <option value="Barangay Secretary">Barangay Secretary</option>
                                <option value="Barangay Treasurer">Barangay Treasurer</option>
                                <option value="Punong Barangay">Punong Barangay</option>
                                <option value="Sanggunian Member">Sanggunian Member</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Department / Office</label>
                        <input type="text" name="dept" class="form-control" placeholder="e.g. Finance Office" required>
                    </div>
                    <div class="form-actions" style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">Create Staff Member</button>
                        <a href="staff-management.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>