<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff' || $_SESSION['role'] !== 'Barangay Treasurer') {
    header('Location: ../../auth/staff-login.php');
    exit();
}

$staff_name = $_SESSION['name'] ?? 'Treasurer';
$staff_role = $_SESSION['role'] ?? 'Barangay Treasurer';
require_once('../../../includes/db_config.php');
require_once('../../../includes/auth_functions.php');

$application_id = intval($_GET['id'] ?? 0);
if ($application_id <= 0) {
    header('Location: applications.php');
    exit();
}

$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['processing_notes'])) {
    $notes_input = trim($_POST['processing_notes'] ?? '');
    $update_query = "UPDATE Applications SET ProcessingNotes = ? WHERE ApplicationID = ?";
    $update_params = array($notes_input, $application_id);
    $update_stmt = sqlsrv_query($conn, $update_query, $update_params);
    if ($update_stmt === false) {
        $error_message = 'Failed to update notes.';
    } else {
        header('Location: view-application.php?id=' . urlencode($application_id));
        exit();
    }
}

$application = getApplicationDetails($application_id);
if (!$application) {
    $error_message = 'Application not found.';
}

$applicant_name = 'Unknown Applicant';
$applicant_email = 'N/A';
if ($application && !empty($application['ClientID'])) {
    $client_query = "SELECT FirstName, LastName, Email FROM Clients WHERE ClientID = ?";
    $client_stmt = sqlsrv_query($conn, $client_query, array($application['ClientID']));
    if ($client_stmt !== false && sqlsrv_has_rows($client_stmt)) {
        $client_row = sqlsrv_fetch_array($client_stmt, SQLSRV_FETCH_ASSOC);
        $applicant_name = trim(($client_row['FirstName'] ?? '') . ' ' . ($client_row['LastName'] ?? '')) ?: 'Unknown Applicant';
        $applicant_email = $client_row['Email'] ?? 'N/A';
    }
}

$created_at = '';
if ($application && isset($application['CreatedAt'])) {
    $created = $application['CreatedAt'];
    if ($created instanceof DateTime) {
        $created_at = $created->format('Y-m-d H:i');
    } else {
        $created_at = date('Y-m-d H:i', strtotime($created));
    }
}

$app_data = [];
if ($application && !empty($application['ApplicationData'])) {
    $app_data = json_decode($application['ApplicationData'], true) ?: [];
}

$status = $application['Status'] ?? 'Unknown';
$status_class = 'badge-secondary';
if ($status === 'Pending') {
    $status_class = 'badge-warning';
} elseif ($status === 'Processing' || $status === 'In Progress') {
    $status_class = 'badge-info';
} elseif ($status === 'Approved' || $status === 'Completed') {
    $status_class = 'badge-success';
} elseif ($status === 'Rejected' || $status === 'Cancelled') {
    $status_class = 'badge-danger';
}

$display_id = '#APP-' . str_pad($application_id, 5, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application - Treasurer Portal</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Treasurer Portal</h3>
                <p class="role-badge"><?php echo htmlspecialchars($staff_role); ?></p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="transactions.php">Transactions</a></li>
                    <li><a href="financial-reports.php">Financial Reports</a></li>
                    <li><a href="budget-management.php">Budget Management</a></li>
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <div>
                        <h2>View Application</h2>
                    </div>
                    <div class="user-info">
                        <span>Welcome, <?php echo htmlspecialchars($staff_name); ?></span>
                        <a href="../../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <a href="applications.php" class="btn btn-secondary">Back to Applications</a>
                <?php else: ?>
                    <section class="staff-section">
                        <h3>Application Details</h3>
                        <div class="info-card">
                            <div class="detail-row">
                                <label>Application ID:</label>
                                <span><?php echo htmlspecialchars($display_id); ?></span>
                            </div>
                            <div class="detail-row">
                                <label>Applicant Name:</label>
                                <span><?php echo htmlspecialchars($applicant_name); ?></span>
                            </div>
                            <div class="detail-row">
                                <label>Email:</label>
                                <span><?php echo htmlspecialchars($applicant_email); ?></span>
                            </div>
                            <div class="detail-row">
                                <label>Service Type:</label>
                                <span><?php echo htmlspecialchars($application['ServiceType'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="detail-row">
                                <label>Date Applied:</label>
                                <span><?php echo htmlspecialchars($created_at); ?></span>
                            </div>
                            <div class="detail-row">
                                <label>Status:</label>
                                <span class="badge <?php echo htmlspecialchars($status_class); ?>"><?php echo htmlspecialchars($status); ?></span>
                            </div>
                            <div class="detail-row">
                                <label>Processing Notes:</label>
                                <div class="announcement-box"><?php echo nl2br(htmlspecialchars($application['ProcessingNotes'] ?? '')); ?></div>
                            </div>

                            <div class="detail-row">
                                <form method="post" action="view-application.php?id=<?php echo urlencode($application_id); ?>">
                                    <label for="processing_notes">Edit Processing Notes (visible to staff):</label>
                                    <textarea id="processing_notes" name="processing_notes" rows="6" style="width:100%;"><?php echo htmlspecialchars($application['ProcessingNotes'] ?? ''); ?></textarea>
                                    <div style="margin-top:8px;">
                                        <button type="submit" class="btn btn-primary">Save Notes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </section>

                    <section class="staff-section">
                        <h3>Form Data</h3>
                        <?php if (!empty($app_data) && is_array($app_data)): ?>
                            <div class="info-card">
                                <?php foreach ($app_data as $key => $value): ?>
                                    <div class="detail-row">
                                        <label><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?>:</label>
                                        <span><?php echo htmlspecialchars(is_array($value) ? json_encode($value) : $value); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No additional application fields were provided.</p>
                        <?php endif; ?>
                    </section>

                    <a href="applications.php" class="btn btn-secondary">Back to Applications</a>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
