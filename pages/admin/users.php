<?php
session_start();
// Validate admin session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/admin-login.php');
    exit();
}

require_once('../../includes/db_config.php');

// Fetch all clients from the database
$sql = "SELECT ClientID, FirstName, LastName, Email, CreatedAt, IsActive FROM Clients ORDER BY CreatedAt DESC";
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
    <title>Manage Users - Admin Dashboard</title>
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
                    <h2>Manage Users</h2>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if(isset($_GET['msg'])): ?>
                    <div class="alert alert-success" style="margin-bottom: 20px;">
                        <?php echo htmlspecialchars($_GET['msg']); ?>
                    </div>
                <?php endif; ?>

                <div class="admin-controls">
                    <input type="text" id="search-users" class="form-control" placeholder="Search residents by name or email...">
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Date Registered</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                            // Correctly format the DateTime object
                            $reg_date = $row['CreatedAt'] instanceof DateTime ? $row['CreatedAt']->format('Y-m-d') : 'N/A';
                            $status_class = $row['IsActive'] ? 'badge-success' : 'badge-danger';
                            $status_text = $row['IsActive'] ? 'Active' : 'Inactive';
                        ?>
                        <tr>
                            <td class="text-bold">#USER-<?php echo str_pad($row['ClientID'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?></td>
                            <td><?php echo htmlspecialchars($row['Email']); ?></td>
                            <td><?php echo $reg_date; ?></td>
                            <td><span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                            <td>
                                <a href="edit-user.php?id=<?php echo $row['ClientID']; ?>" class="btn btn-xs btn-warning">Edit</a>
                                <?php if($row['IsActive']): ?>
                                    <a href="user-actions.php?action=deactivate&id=<?php echo $row['ClientID']; ?>" 
                                       class="btn btn-xs btn-danger" 
                                       onclick="return confirm('Are you sure you want to deactivate this account?')">Deactivate</a>
                                <?php else: ?>
                                    <a href="user-actions.php?action=activate&id=<?php echo $row['ClientID']; ?>" 
                                       class="btn btn-xs btn-success">Activate</a>
                                <?php endif; ?>
                                <a href="user-actions.php?action=remove&id=<?php echo $row['ClientID']; ?>" 
                                   class="btn btn-xs btn-danger" 
                                   onclick="return confirm('WARNING: This will permanently delete the user and ALL their applications. This action cannot be undone. Are you sure?')" 
                                   style="background-color: #dc3545; border-color: #dc3545;">Remove</a>
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