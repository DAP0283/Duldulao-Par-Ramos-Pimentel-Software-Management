<?php
session_start();
// Validate admin session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../auth/admin-login.php');
    exit();
}

require_once('../../includes/db_config.php');

$status_filter = $_GET['status'] ?? '';

// Build Query: Join with Clients and Staff
$sql = "SELECT a.ApplicationID, a.ServiceType, a.Status, a.CreatedAt, 
               c.FirstName, c.LastName, 
               s.FirstName as StaffF, s.LastName as StaffL 
        FROM Applications a 
        INNER JOIN Clients c ON a.ClientID = c.ClientID 
        LEFT JOIN Staff s ON a.AssignedToStaffID = s.StaffID";

if (!empty($status_filter)) {
    $sql .= " WHERE a.Status = ?";
    $stmt = sqlsrv_query($conn, $sql, array($status_filter));
} else {
    $stmt = sqlsrv_query($conn, $sql);
}

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Applications - Admin Dashboard</title>
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
                    <h2>All Applications</h2>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="admin-controls">
                    <form method="GET" action="">
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option value="">Filter by Status: All</option>
                            <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Processing" <?php echo $status_filter === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </form>
                </div>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Application ID</th>
                            <th>Applicant Name</th>
                            <th>Service Type</th>
                            <th>Date Applied</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                            // Consistent Badge Logic
                            $badge_class = match($row['Status']) {
                                'Approved', 'Completed' => 'badge-success',
                                'Rejected' => 'badge-danger',
                                'Processing' => 'badge-info',
                                default => 'badge-warning' // Matches dashboard color
                            };

                            // Correctly format the DateTime object
                            $date_applied = $row['CreatedAt'] instanceof DateTime ? $row['CreatedAt']->format('Y-m-d') : 'N/A';
                            
                            $staff_name = $row['StaffF'] ? $row['StaffF'] . ' ' . $row['StaffL'] : 'Unassigned';
                        ?>
                        <tr>
                            <td class="text-bold">#APP-<?php echo str_pad($row['ApplicationID'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']); ?></td>
                            <td><?php echo htmlspecialchars($row['ServiceType']); ?></td>
                            <td><?php echo $date_applied; ?></td>
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($row['Status']); ?></span></td>
                            <td><?php echo htmlspecialchars($staff_name); ?></td>
                            <td>
                                <a href="view-application.php?id=<?php echo $row['ApplicationID']; ?>" class="btn btn-xs btn-info">View/Process</a>
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