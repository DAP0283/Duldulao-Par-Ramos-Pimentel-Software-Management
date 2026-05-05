<?php
/**
 * Client - View Application Details
 */
session_start();

// Include database functions
require_once('../../includes/db_config.php');
require_once('../../includes/auth_functions.php');

// Validate client session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../../auth/client-login.php');
    exit();
}

// Get user information from session
$user_name = $_SESSION['name'];

// Get application ID from URL
$app_id_param = $_GET['id'] ?? '';
// Remove #APP- prefix if present
$application_id = str_replace('#APP-', '', $app_id_param);
$application_id = intval($application_id);

// Fetch application details from database
$application = null;
$error_message = '';

if ($application_id > 0) {
    $application = getApplicationDetails($application_id);
    
    if (!$application) {
        $error_message = 'Application not found';
    }
} else {
    $error_message = 'Invalid application ID';
}

// Parse application data (stored as JSON)
$app_data = array();
if ($application && isset($application['ApplicationData'])) {
    $app_data = json_decode($application['ApplicationData'], true) ?? [];
}

// Format dates
$date_applied = '';
if ($application && isset($application['CreatedAt'])) {
    $created = $application['CreatedAt'];
    if ($created instanceof DateTime) {
        $date_applied = $created->format('Y-m-d');
    } else {
        $date_applied = date('Y-m-d', strtotime($created));
    }
}

$approval_date = '';
if ($application && isset($application['ApprovalDate']) && $application['ApprovalDate']) {
    $approved = $application['ApprovalDate'];
    if ($approved instanceof DateTime) {
        $approval_date = $approved->format('Y-m-d');
    } else {
        $approval_date = date('Y-m-d', strtotime($approved));
    }
}

// Format status with badge class
$status = isset($application['Status']) ? $application['Status'] : 'Pending';
$status_class = 'badge-secondary';
if ($status === 'Pending') $status_class = 'badge-warning';
elseif ($status === 'Processing' || $status === 'In Progress') $status_class = 'badge-info';
elseif ($status === 'Approved' || $status === 'Completed') $status_class = 'badge-success';
elseif ($status === 'Rejected' || $status === 'Cancelled') $status_class = 'badge-danger';

// Format application display ID
$app_id_display = '#APP-' . str_pad($application_id, 5, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application - Barangay e-Services</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Barangay e-Services</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="my-applications.php" class="active">My Applications</a></li>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Application Details</h2>
                    <div class="user-info">
                        <a href="my-applications.php" class="btn btn-sm btn-secondary">Back to Applications</a>
                        <a href="../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                    <a href="my-applications.php" class="btn btn-secondary">Back to Applications</a>
                <?php else: ?>
                <div class="form-container">
                    <section class="application-details">
                        <h3>Application <?php echo htmlspecialchars($app_id_display); ?></h3>

                        <div class="detail-row">
                            <label>Application ID:</label>
                            <span><?php echo htmlspecialchars($app_id_display); ?></span>
                        </div>

                        <div class="detail-row">
                            <label>Service Type:</label>
                            <span><?php echo htmlspecialchars(isset($application['ServiceType']) ? $application['ServiceType'] : 'N/A'); ?></span>
                        </div>

                        <div class="detail-row">
                            <label>Date Applied:</label>
                            <span><?php echo htmlspecialchars($date_applied); ?></span>
                        </div>

                        <div class="detail-row">
                            <label>Current Status:</label>
                            <span><span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span></span>
                        </div>

                        <?php if (isset($application['ApprovalDate']) && $application['ApprovalDate'] && $approval_date): ?>
                        <div class="detail-row">
                            <label>Approval Date:</label>
                            <span><?php echo htmlspecialchars($approval_date); ?></span>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($application['ApprovedBy']) && $application['ApprovedBy']): ?>
                        <div class="detail-row">
                            <label>Approved By:</label>
                            <span><?php echo htmlspecialchars($application['ApprovedBy']); ?></span>
                        </div>
                        <?php endif; ?>

                        <h3 style="margin-top: 2rem;">Application Details</h3>

                        <?php if ($app_data): ?>
                            <?php foreach ($app_data as $key => $value): ?>
                            <div class="detail-row">
                                <label><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?>:</label>
                                <span><?php echo htmlspecialchars($value); ?></span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (isset($application['ProcessingNotes']) && $application['ProcessingNotes']): ?>
                        <h3 style="margin-top: 2rem;">Processing Notes</h3>
                        <div class="announcement-box">
                            <p><?php echo htmlspecialchars($application['ProcessingNotes']); ?></p>
                        </div>
                        <?php endif; ?>
                    </section>

                    <div class="form-actions">
                        <button type="button" class="btn btn-primary" onclick="printDocument()">Print</button>
                        <a href="my-applications.php" class="btn btn-secondary">Back to Applications</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
