<?php
session_start();
// Validate admin session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/admin-login.php');
    exit();
}

require_once('../../includes/db_config.php');

// Fetch staff details from your schema
$sql = "SELECT StaffID, FirstName, LastName, Position, Department, Email, IsActive FROM Staff ORDER BY CreatedAt DESC";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - Admin Dashboard</title>
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
                    <li><a href="staff-management.php" class="active">Staff Management</a></li>
                    <li><a href="settings.php">System Settings</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Staff Management</h2>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if(isset($_GET['msg'])): ?>
                    <div class="alert alert-success" style="margin-bottom: 20px;">
                        <?php echo htmlspecialchars($_GET['msg']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="admin-controls">
                    <a href="add-staff.php" class="btn btn-primary">Add New Staff Member</a>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Staff ID</th>
                            <th>Name</th>
                            <th>Position & Department</th>
                            <th>Email Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { 
                            $status_class = $row['IsActive'] ? 'badge-success' : 'badge-danger';
                            $status_text = $row['IsActive'] ? 'Active' : 'Inactive';
                        ?>
                        <tr>
                            <td class="text-bold">#STAFF-<?php echo str_pad($row['StaffID'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['Position']); ?></strong><br>
                                <small style="color: #666;"><?php echo htmlspecialchars($row['Department'] ?? 'General Administration'); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['Email']); ?></td>
                            <td><span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                            <td>
                                <a href="edit-staff.php?id=<?php echo $row['StaffID']; ?>" class="btn btn-xs btn-warning">Edit</a>
                                <?php if($row['IsActive']): ?>
                                    <a href="staff-actions.php?action=deactivate&id=<?php echo $row['StaffID']; ?>" 
                                       class="btn btn-xs btn-danger" 
                                       onclick="return confirm('Deactivate this staff account?')">Deactivate</a>
                                <?php else: ?>
                                    <a href="staff-actions.php?action=activate&id=<?php echo $row['StaffID']; ?>" 
                                       class="btn btn-xs btn-success">Activate</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script src="../../assets/js/main.js"></script>
</body>
</html>