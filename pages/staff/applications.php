<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: ../../auth/staff-login.php');
    exit();
}

$staff_name = $_SESSION['name'] ?? 'Staff';
$staff_role = $_SESSION['role'] ?? '';
$allowed_roles = ['Barangay Secretary', 'Barangay Treasurer', 'Punong Barangay', 'Sangguian Member'];

// Check if current user's role is allowed to view applications
if (!in_array($staff_role, $allowed_roles)) {
    header('Location: ../../auth/staff-login.php');
    exit();
}

require_once('../../includes/db_config.php');
require_once('../../includes/auth_functions.php');

$status_filter = $_GET['status'] ?? '';

// Fetch applications from database
$applications = array();
$query = "SELECT a.ApplicationID, a.ClientID, a.ServiceType, a.Status, a.CreatedAt, c.FirstName, c.LastName
          FROM Applications a
          LEFT JOIN Clients c ON a.ClientID = c.ClientID";

if ($status_filter !== '') {
    $status_filter = strtolower($status_filter);
    if ($status_filter === 'pending') {
        $query .= " WHERE a.Status = ?";
        $params = array('Pending');
    } elseif ($status_filter === 'approved') {
        $query .= " WHERE a.Status = ?";
        $params = array('Approved');
    } elseif ($status_filter === 'rejected') {
        $query .= " WHERE a.Status = ?";
        $params = array('Rejected');
    } elseif ($status_filter === 'in-progress' || $status_filter === 'processing') {
        $query .= " WHERE a.Status IN (?, ?)";
        $params = array('In Progress', 'Processing');
    } elseif ($status_filter === 'completed') {
        $query .= " WHERE a.Status IN (?, ?)";
        $params = array('Approved', 'Completed');
    }

    if (isset($params)) {
        $stmt = sqlsrv_query($conn, $query, $params);
    } else {
        $stmt = sqlsrv_query($conn, $query);
    }
} else {
    $stmt = sqlsrv_query($conn, $query);
}

if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Format date
        $created = $row['CreatedAt'];
        if ($created instanceof DateTime) {
            $date_formatted = $created->format('Y-m-d');
        } else {
            $date_formatted = date('Y-m-d', strtotime($created));
        }
        
        $applications[] = array(
            'id' => '#APP-' . str_pad($row['ApplicationID'], 5, '0', STR_PAD_LEFT),
            'application_id' => $row['ApplicationID'],
            'applicant' => ($row['FirstName'] ?? 'Unknown') . ' ' . ($row['LastName'] ?? ''),
            'service' => $row['ServiceType'],
            'date' => $date_formatted,
            'status' => $row['Status']
        );
    }
}

// Determine permissions based on role
$can_process = in_array($staff_role, ['Barangay Secretary', 'Punong Barangay']);
$can_view_only = in_array($staff_role, ['Sangguian Member', 'Barangay Treasurer']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    <?php if (!in_array($staff_role, ['Sangguian Member'])): ?>
                    <li><a href="clients.php">Clients</a></li>
                    <?php endif; ?>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <div>
                        <h2>Applications<?php if ($can_view_only): ?> (View Only)<?php endif; ?></h2>
                    </div>
                    <div class="user-info">
                        <span>Welcome, <?php echo htmlspecialchars($staff_name); ?></span>
                        <a href="../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>
            <div class="dashboard-content">
                <div class="admin-controls">
                    <select id="filter-status" class="form-control" onchange="filterApplications(this.value)">
                        <option value="">All Applications</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <section class="staff-section">
                    <h3>All Applications</h3>
                    <?php if ($can_view_only): ?>
                    <p><em>You have view-only access to applications</em></p>
                    <?php endif; ?>
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Applicant</th>
                                <th>Service</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($applications) > 0): ?>
                                <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($app['id']); ?></td>
                                    <td><?php echo htmlspecialchars($app['applicant']); ?></td>
                                    <td><?php echo htmlspecialchars($app['service']); ?></td>
                                    <td><?php echo htmlspecialchars($app['date']); ?></td>
                                    <td><?php echo htmlspecialchars($app['status']); ?></td>
                                    <td>
                                        <?php if ($can_process): ?>
                                            <?php if ($staff_role === 'Barangay Secretary'): ?>
                                                <a href="secretary/process-application.php?id=<?php echo urlencode($app['application_id']); ?>" class="btn btn-xs btn-primary">Process</a>
                                            <?php else: ?>
                                                <a href="punong-barangay/process-application.php?id=<?php echo urlencode($app['application_id']); ?>" class="btn btn-xs btn-primary">Process</a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="view-application.php?id=<?php echo urlencode($app['application_id']); ?>" class="btn btn-xs btn-secondary">View</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6">No applications found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </div>
    <script src="../../assets/js/main.js"></script>
    <script>
        function filterApplications(status) {
            if (status === '') {
                window.location.href = 'applications.php';
            } else {
                window.location.href = 'applications.php?status=' + encodeURIComponent(status);
            }
        }
    </script>
</body>
</html>
