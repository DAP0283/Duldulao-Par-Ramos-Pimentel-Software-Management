<?php
/**
 * Process Application - Punong Barangay
 */
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff' || $_SESSION['role'] !== 'Punong Barangay') {
    header('Location: ../../../auth/staff-login.php');
    exit();
}

require_once('../../../includes/db_config.php');
require_once('../../../includes/auth_functions.php');

$staff_name = $_SESSION['name'];
$staff_id = $_SESSION['user_id'];
$application_id = $_GET['id'] ?? $_POST['application_id'] ?? 0;
$application_id = intval($application_id);

$application = null;
$error_message = '';
$success_message = '';

// Fetch application details
if ($application_id > 0) {
    $application = getApplicationDetails($application_id);
    
    if (!$application) {
        $error_message = 'Application not found';
    }
} else {
    $error_message = 'Invalid application ID';
}

// Handle application processing (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $application) {
    $action = $_POST['action'] ?? '';
    $status = $_POST['status'] ?? '';
    $processing_notes = $_POST['processing_notes'] ?? '';
    
    if ($action === 'update' && !empty($status)) {
        // Update application status in database
        $update_query = "UPDATE Applications SET Status = ?, ProcessingNotes = ?, UpdatedAt = GETDATE() WHERE ApplicationID = ?";
        $update_params = array($status, $processing_notes, $application_id);
        $update_stmt = sqlsrv_query($conn, $update_query, $update_params);
        
        if ($update_stmt !== false) {
            $success_message = 'Application status updated successfully';
            // Refresh application data
            $application = getApplicationDetails($application_id);
        } else {
            $error_message = 'Failed to update application status';
        }
    }
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
        $date_applied = $created->format('Y-m-d H:i');
    } else {
        $date_applied = date('Y-m-d H:i', strtotime($created));
    }
}

// Get applicant info
$applicant_name = '';
$applicant_email = '';
$client_id = $application['ClientID'] ?? 0;

if ($client_id > 0) {
    $client_query = "SELECT FirstName, LastName, Email FROM Clients WHERE ClientID = ?";
    $client_stmt = sqlsrv_query($conn, $client_query, array($client_id));
    if ($client_stmt !== false && sqlsrv_has_rows($client_stmt)) {
        $client_row = sqlsrv_fetch_array($client_stmt, SQLSRV_FETCH_ASSOC);
        $applicant_name = ($client_row['FirstName'] ?? '') . ' ' . ($client_row['LastName'] ?? '');
        $applicant_email = $client_row['Email'] ?? '';
    }
}

// Format application display ID
$app_id_display = '#APP-' . str_pad($application_id, 5, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Application - Barangay e-Services</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Barangay Executive</h3>
                <p class="role-badge">Punong Barangay</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="staff-management.php">Staff Management</a></li>
                    <li><a href="staff-roles.php">Roles & Permissions</a></li>
                    <li><a href="applications.php" class="active">Applications</a></li>
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Process Application</h2>
                    <a href="applications.php" class="btn btn-sm btn-secondary">Back to Applications</a>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                    <a href="applications.php" class="btn btn-secondary">Back to Applications</a>
                <?php else: ?>
                    <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                    <?php endif; ?>

                    <div class="form-container">
                        <section class="application-details">
                            <h3>Application <?php echo htmlspecialchars($app_id_display); ?></h3>

                            <div class="detail-row">
                                <label>Serial Number:</label>
                                <span><?php echo htmlspecialchars($app_id_display); ?></span>
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
                                <span><?php echo htmlspecialchars(isset($application['ServiceType']) ? $application['ServiceType'] : 'N/A'); ?></span>
                            </div>

                            <div class="detail-row">
                                <label>Date Applied:</label>
                                <span><?php echo htmlspecialchars($date_applied); ?></span>
                            </div>

                            <div class="detail-row">
                                <label>Current Status:</label>
                                <span>
                                    <?php 
                                        $status = isset($application['Status']) ? $application['Status'] : 'Pending';
                                        $status_class = 'badge-secondary';
                                        if ($status === 'Pending') $status_class = 'badge-warning';
                                        elseif ($status === 'Processing' || $status === 'In Progress') $status_class = 'badge-info';
                                        elseif ($status === 'Approved') $status_class = 'badge-success';
                                        elseif ($status === 'Rejected') $status_class = 'badge-danger';
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span>
                                </span>
                            </div>

                            <h3 style="margin-top: 2rem;">Application Form Data</h3>
                            <?php if ($app_data): ?>
                                <?php foreach ($app_data as $key => $value): ?>
                                <div class="detail-row">
                                    <label><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?>:</label>
                                    <span><?php echo htmlspecialchars($value); ?></span>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No application data available</p>
                            <?php endif; ?>

                            <?php if (isset($application['ProcessingNotes']) && $application['ProcessingNotes']): ?>
                            <h3 style="margin-top: 2rem;">Previous Notes</h3>
                            <div class="announcement-box">
                                <p><?php echo htmlspecialchars($application['ProcessingNotes']); ?></p>
                            </div>
                            <?php endif; ?>
                        </section>

                        <form method="POST" class="form-container" style="margin-top: 2rem;">
                            <h3>Update Application Status</h3>

                            <input type="hidden" name="application_id" value="<?php echo $application_id; ?>">
                            <input type="hidden" name="action" value="update">

                            <div class="form-group">
                                <label for="status">New Status:</label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="">-- Select Status --</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Processing">Processing</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Rejected">Rejected</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="processing_notes">Processing Notes:</label>
                                <textarea id="processing_notes" name="processing_notes" class="form-control" rows="4" placeholder="Add any comments or notes for this application..."></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Update Application</button>
                                <a href="applications.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../../../assets/js/main.js"></script>
</body>
</html>
