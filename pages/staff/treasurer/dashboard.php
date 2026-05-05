<?php
/**
 * Barangay Treasurer Dashboard
 * Monitors and records financial transactions
 */
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: ../../../auth/staff-login.php');
    exit();
}

$staff_role = $_SESSION['role'] ?? '';
if ($staff_role !== 'Barangay Treasurer') {
    header('Location: ../dashboard.php');
    exit();
}

require_once('../../../includes/db_config.php');
require_once('../../../includes/auth_functions.php');

$staff_id = $_SESSION['user_id'];
$staff_name = $_SESSION['name'];

$total_transactions = 0;
$pending_approvals = 0;

$trans_result = sqlsrv_query($conn, "SELECT COUNT(*) as Count FROM Transactions WHERE CreatedAt >= DATEADD(MONTH, -1, GETDATE())");
if ($trans_result !== false) {
    $row = sqlsrv_fetch_array($trans_result, SQLSRV_FETCH_ASSOC);
    $total_transactions = $row['Count'] ?? 0;
}

$recent_trans_query = "SELECT TOP 5 TransactionID, Description, Amount, TransactionType, Status, CreatedAt 
                       FROM Transactions 
                       WHERE CreatedAt >= DATEADD(MONTH, -1, GETDATE())
                       ORDER BY CreatedAt DESC";
$recent_trans = sqlsrv_query($conn, $recent_trans_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treasurer Dashboard - Barangay e-Services</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Treasurer Portal</h3>
                <p class="role-badge">Barangay Treasurer</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
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
                    <h2>Welcome, <?php echo htmlspecialchars($staff_name); ?></h2>
                    <div class="user-info">
                        <a href="../../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <section class="stats-section">
                    <div class="stat-card stat-card-primary">
                        <h4>Transactions (30 days)</h4>
                        <p class="stat-number"><?php echo $total_transactions; ?></p>
                        <a href="transactions.php">View All</a>
                    </div>
                    <div class="stat-card stat-card-info">
                        <h4>Pending Remarks</h4>
                        <p class="stat-number"><?php echo $pending_approvals; ?></p>
                        <a href="transactions.php?status=pending">Review</a>
                    </div>
                    <div class="stat-card stat-card-success">
                        <h4>Financial Reports</h4>
                        <p class="stat-number">Monthly</p>
                        <a href="financial-reports.php">Generate</a>
                    </div>
                </section>

                <section class="staff-section">
                    <h3>Quick Actions</h3>
                    <div class="action-buttons">
                        <a href="transactions.php" class="btn btn-primary">Record Transaction</a>
                        <a href="financial-reports.php" class="btn btn-primary">View Reports</a>
                        <a href="budget-management.php" class="btn btn-secondary">Manage Budget</a>
                    </div>
                </section>

                <section class="staff-section">
                    <h3>Recent Transactions</h3>
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($recent_trans !== false) {
                                $found = false;
                                while ($row = sqlsrv_fetch_array($recent_trans, SQLSRV_FETCH_ASSOC)) {
                                    $found = true;
                                    $status_badge = 'badge-warning';
                                    if ($row['Status'] === 'Approved') {
                                        $status_badge = 'badge-success';
                                    } elseif ($row['Status'] === 'Rejected') {
                                        $status_badge = 'badge-danger';
                                    }
                            ?>
                            <tr>
                                <td>#TRX-<?php echo str_pad($row['TransactionID'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($row['Description']); ?></td>
                                <td><?php echo htmlspecialchars($row['TransactionType']); ?></td>
                                <td>₱<?php echo number_format($row['Amount'], 2); ?></td>
                                <td><span class="badge <?php echo $status_badge; ?>"><?php echo htmlspecialchars($row['Status']); ?></span></td>
                                <td><?php echo ($row['CreatedAt'] instanceof DateTime) ? $row['CreatedAt']->format('Y-m-d') : date('Y-m-d', strtotime($row['CreatedAt'])); ?></td>
                            </tr>
                            <?php }
                                if (!$found) {
                                    echo '<tr><td colspan="6">No transactions found</td></tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6">Unable to load transactions</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </div>

    <script src="../../../assets/js/main.js"></script>
</body>
</html>
