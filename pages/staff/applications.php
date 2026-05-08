<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: ../../auth/staff-login.php');
    exit();
}

$staff_role = trim($_SESSION['role'] ?? '');
$staff_name = $_SESSION['name'] ?? 'Staff';

// Fixed spelling: "Sanggunian" instead of "Sangguian"
$allowed_roles = ['Barangay Secretary', 'Barangay Treasurer', 'Punong Barangay', 'Sanggunian Member'];

if (!in_array($staff_role, $allowed_roles)) {
    header('Location: ../../auth/staff-login.php');
    exit();
}

require_once('../../includes/db_config.php');

$status_filter = $_GET['status'] ?? '';
$applications = [];

// Base Query
$query = "SELECT a.ApplicationID, a.ServiceType, a.Status, a.CreatedAt, c.FirstName, c.LastName 
          FROM Applications a LEFT JOIN Clients c ON a.ClientID = c.ClientID";

if (!empty($status_filter)) {
    $query .= " WHERE a.Status = ?";
    $stmt = sqlsrv_query($conn, $query, [$status_filter]);
} else {
    $stmt = sqlsrv_query($conn, $query);
}

if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $applications[] = $row;
    }
}

// Access Control Logic
$can_process = in_array($staff_role, ['Barangay Secretary', 'Punong Barangay']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Applications - Barangay e-Services</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Staff Portal</h3>
                <p class="role-badge"><?php echo htmlspecialchars($staff_role); ?></p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="applications.php" class="active">Applications</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Applications</h2>
                    <div class="user-info">
                        <span>Welcome, <strong><?php echo htmlspecialchars($staff_name); ?></strong></span>
                        <a href="../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="admin-controls">
                    <select class="form-control" onchange="location.href='?status='+this.value">
                        <option value="">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>

                <section class="staff-section">
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Applicant</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                            <tr>
                                <td>#APP-<?php echo str_pad($app['ApplicationID'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($app['FirstName'].' '.$app['LastName']); ?></td>
                                <td><?php echo htmlspecialchars($app['ServiceType']); ?></td>
                                <td><?php echo htmlspecialchars($app['Status']); ?></td>
                                <td>
                                    <?php if ($can_process): ?>
                                        <a href="process-application.php?id=<?php echo $app['ApplicationID']; ?>" class="btn btn-xs btn-primary">Process</a>
                                    <?php else: ?>
                                        <a href="view-application.php?id=<?php echo $app['ApplicationID']; ?>" class="btn btn-xs btn-secondary">View</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </div>
</body>
</html>