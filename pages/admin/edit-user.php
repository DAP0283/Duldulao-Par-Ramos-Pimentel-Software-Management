<?php
session_start();
require_once('../../includes/db_config.php');

$client_id = $_GET['id'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE Clients SET FirstName = ?, MiddleName = ?, LastName = ?, Email = ?, PhoneNumber = ?, Address = ?, Gender = ?, CivilStatus = ?, Occupation = ?, UpdatedAt = GETDATE() WHERE ClientID = ?";
    $params = array(
        $_POST['fname'],
        $_POST['mname'],
        $_POST['lname'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['address'],
        $_POST['gender'],
        $_POST['civil'],
        $_POST['job'],
        $client_id
    );
    sqlsrv_query($conn, $sql, $params);
    header("Location: users.php?msg=Resident profile fully updated."); exit();
}

$res = sqlsrv_query($conn, "SELECT * FROM Clients WHERE ClientID = ?", array($client_id));
$u = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Resident - Admin Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><h3>Admin Panel</h3></div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="users.php" class="active">Manage Users</a></li>
                    <li><a href="applications.php">All Applications</a></li>
                    <li><a href="staff-management.php">Staff Management</a></li>
                    <li><a href="settings.php">System Settings</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Edit Resident Profile: #USER-<?php echo str_pad($client_id, 3, '0', STR_PAD_LEFT); ?></h2>
                </div>
            </header>

            <div class="dashboard-content">
                <form method="POST" class="form-container">
                    <div class="form-row">
                        <div class="form-group"><label>First Name</label><input type="text" name="fname" value="<?php echo htmlspecialchars($u['FirstName']); ?>" class="form-control"></div>
                        <div class="form-group"><label>Middle Name</label><input type="text" name="mname" value="<?php echo htmlspecialchars($u['MiddleName']); ?>" class="form-control"></div>
                        <div class="form-group"><label></label>Last Name</label><input type="text" name="lname" value="<?php echo htmlspecialchars($u['LastName']); ?>" class="form-control"></div>
                    </div>
                    <div class="form-group"><label>Email Address</label><input type="email" name="email" value="<?php echo htmlspecialchars($u['Email']); ?>" class="form-control"></div>
                    
                    <div class="form-row">
                        <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?php echo htmlspecialchars($u['PhoneNumber']); ?>" class="form-control"></div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="gender" class="form-control">
                                <option value="Male" <?php echo ($u['Gender']=='Male'?'selected':'');?>>Male</option>
                                <option value="Female" <?php echo ($u['Gender']=='Female'?'selected':'');?>>Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group"><label>Civil Status</label><input type="text" name="civil" value="<?php echo htmlspecialchars($u['CivilStatus']); ?>" class="form-control"></div>
                        <div class="form-group"><label>Occupation</label><input type="text" name="job" value="<?php echo htmlspecialchars($u['Occupation']); ?>" class="form-control"></div>
                    </div>

                    <div class="form-group"><label>Address</label><input type="text" name="address" value="<?php echo htmlspecialchars($u['Address']); ?>" class="form-control"></div>
                    
                    <button type="submit" class="btn btn-primary">Save Complete Profile</button>
                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </main>
    </div>
</body>
</html>