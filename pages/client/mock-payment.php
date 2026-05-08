<?php
/**
 * Mock Payment Page (Client)
 */
session_start();

require_once('../../includes/db_config.php');
require_once('../../includes/auth_functions.php');
require_once('../../includes/db_functions_enhanced.php');

// Validate client session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../../auth/client-login.php');
    exit();
}

// Get application id from query
$app_id_param = $_GET['id'] ?? '';
$application_id = str_replace('#APP-', '', $app_id_param);
$application_id = intval($application_id);

if ($application_id <= 0) {
    header('Location: view-application.php?id=' . urlencode($app_id_param) . '&error=invalid_id');
    exit();
}

// Fetch application
$application = getApplicationDetails($application_id);
if (!$application || $application['ClientID'] != $_SESSION['user_id']) {
    header('Location: view-application.php?id=' . urlencode($app_id_param) . '&error=not_found');
    exit();
}

$status = $application['Status'] ?? 'Pending';

// Only allow mock payment when processing
if (!($status === 'Processing' || $status === 'In Progress')) {
    header('Location: view-application.php?id=' . urlencode($app_id_param) . '&error=invalid_status');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'] ?? '';
    $method = $_POST['method'] ?? '';

    if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
        $error = 'Please enter a valid amount.';
    } else {
        // Create mock transaction id
        $txid = 'MOCK-' . strtoupper(bin2hex(random_bytes(4)));

        // Store payment in Payments table via helper
        $create = createPayment($application_id, $_SESSION['user_id'], (float)$amount, $method, $txid);
        if ($create['success']) {
            // Optionally append a short note to processing notes
            $manilaTz = new DateTimeZone('Asia/Manila');
            $now = (new DateTime('now', $manilaTz))->format('Y-m-d H:i:s');
            $payment_note = "Payment recorded: PHP " . number_format((float)$amount, 2) . " via " . htmlspecialchars($method) . " | TxID: " . $txid . " | " . $now;
            $existing_notes = $application['ProcessingNotes'] ?? '';
            $new_notes = trim($existing_notes . "\n" . $payment_note);
            // Update application processing notes (keep same status)
            updateApplicationStatus($application_id, $status, $new_notes, null);

            // Redirect back to application view with success
            header('Location: view-application.php?id=' . urlencode($app_id_param) . '&payment_success=1');
            exit();
        } else {
            $error = 'Failed to record payment: ' . ($create['message'] ?? 'Unknown error');
        }
    }
}

// Display simple mock payment form
$app_display = '#APP-' . str_pad($application_id, 5, '0', STR_PAD_LEFT);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock Payment - Barangay e-Services</title>
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
                    <li><a href="my-applications.php">My Applications</a></li>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="../../auth/2fa-setup.php">Security Settings</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Mock Payment for <?php echo htmlspecialchars($app_display); ?></h2>
                    <div class="user-info">
                        <a href="view-application.php?id=<?php echo urlencode($app_display); ?>" class="btn btn-sm btn-secondary">Back to Application</a>
                        <a href="../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="form-container">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="amount">Amount (PHP)</label>
                            <input type="number" step="0.01" min="0" id="amount" name="amount" required class="form-control" placeholder="0.00">
                        </div>

                        <div class="form-group">
                            <label for="method">Payment Method</label>
                            <select id="method" name="method" class="form-control">
                                <option value="GCash">GCash (mock)</option>
                                <option value="PayMaya">PayMaya (mock)</option>
                                <option value="CreditCard">Credit Card (mock)</option>
                                <option value="Cash">Cash on Pickup (mock)</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Pay (Mock)</button>
                            <a href="view-application.php?id=<?php echo urlencode($app_display); ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>

                    <p style="margin-top:1rem; font-size:0.9rem; color:#666;">This is a mock payment flow for testing purposes only. No real payment will be processed.</p>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
