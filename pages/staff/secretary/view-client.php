<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff' || $_SESSION['role'] !== 'Barangay Secretary') {
    header('Location: ../../../auth/staff-login.php');
    exit();
}

require_once('../../../includes/db_config.php');

$client_id = $_GET['id'] ?? 0;
$client_id = intval($client_id);

$client = null;
$error_message = '';

// Fetch client details
if ($client_id > 0) {
    $query = "SELECT ClientID, FirstName, LastName, Email, PhoneNumber, Address, CreatedAt FROM Clients WHERE ClientID = ?";
    $stmt = sqlsrv_query($conn, $query, array($client_id));
    
    if ($stmt !== false && sqlsrv_has_rows($stmt)) {
        $client = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    } else {
        $error_message = 'Client not found';
    }
} else {
    $error_message = 'Invalid client ID';
}

// Format date
$date_joined = '';
if ($client && isset($client['CreatedAt'])) {
    $created = $client['CreatedAt'];
    if ($created instanceof DateTime) {
        $date_joined = $created->format('Y-m-d H:i');
    } else {
        $date_joined = date('Y-m-d H:i', strtotime($created));
    }
}

// Fetch client applications count
$app_count = 0;
if ($client_id > 0) {
    $count_query = "SELECT COUNT(*) as Count FROM Applications WHERE ClientID = ?";
    $count_stmt = sqlsrv_query($conn, $count_query, array($client_id));
    if ($count_stmt !== false) {
        $count_row = sqlsrv_fetch_array($count_stmt, SQLSRV_FETCH_ASSOC);
        $app_count = $count_row['Count'] ?? 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Client - Barangay e-Services</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Secretary Portal</h3>
                <p class="role-badge">Barangay Secretary</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="clients.php">Clients</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>View Client</h2>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                    <a href="clients.php" class="btn btn-secondary">Back to Clients</a>
                <?php else: ?>
                    <div class="form-container">
                        <section class="application-details">
                            <h3>Client Information</h3>

                            <div class="detail-row">
                                <label>Client ID:</label>
                                <span><?php echo htmlspecialchars($client_id); ?></span>
                            </div>

                            <div class="detail-row">
                                <label>Full Name:</label>
                                <span><?php echo htmlspecialchars(($client['FirstName'] ?? '') . ' ' . ($client['LastName'] ?? '')); ?></span>
                            </div>

                            <div class="detail-row">
                                <label>Email:</label>
                                <span><?php echo htmlspecialchars($client['Email'] ?? 'N/A'); ?></span>
                            </div>

                            <div class="detail-row">
                                <label>Phone Number:</label>
                                <span><?php echo htmlspecialchars($client['PhoneNumber'] ?? 'N/A'); ?></span>
                            </div>

                            <div class="detail-row">
                                <label>Address:</label>
                                <span><?php echo htmlspecialchars($client['Address'] ?? 'N/A'); ?></span>
                            </div>

                            <div class="detail-row">
                                <label>Member Since:</label>
                                <span><?php echo htmlspecialchars($date_joined); ?></span>
                            </div>

                            <div class="detail-row">
                                <label>Total Applications:</label>
                                <span><?php echo $app_count; ?></span>
                            </div>
                        </section>

                        <div style="margin-top: 2rem;">
                            <a href="clients.php" class="btn btn-secondary">Back to Clients</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="../../../assets/js/main.js"></script>
</body>
</html>
