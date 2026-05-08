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
require_once('../../../includes/news_functions.php');
require_once('../../../includes/db_functions_enhanced.php');

// Fetch Philippine News
$news = getCachedPhilippineNews(5);
if (empty($news)) {
    $news = getFallbackNews(5);
}

$staff_id = $_SESSION['user_id'];
$staff_name = $_SESSION['name'];

$recent_payments = getRecentPayments(30);
$total_transactions = is_array($recent_payments) ? count($recent_payments) : 0;
// pending_approvals left as 0 unless additional logic added
$pending_approvals = 0;
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
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="dashboard-content-main">

                <section class="staff-section">
                    <h3>Recent Payments</h3>
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Application</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Transaction</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (is_array($recent_payments)) {
                                if (count($recent_payments) === 0) {
                                    echo '<tr><td colspan="6">No payments found</td></tr>';
                                } else {
                                    foreach ($recent_payments as $row) {
                                        $created = $row['CreatedAt'];
                                        $date = ($created instanceof DateTime) ? $created->format('Y-m-d') : date('Y-m-d', strtotime($created));
                            ?>
                            <tr>
                                <td>#PAY-<?php echo str_pad($row['PaymentID'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><a href="view-application.php?id=<?php echo urlencode($row['ApplicationID']); ?>">#APP-<?php echo str_pad($row['ApplicationID'],5,'0',STR_PAD_LEFT); ?></a></td>
                                <td><?php echo htmlspecialchars($row['Method']); ?></td>
                                <td>₱<?php echo number_format($row['Amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['TransactionID']); ?></td>
                                <td><?php echo htmlspecialchars($date); ?></td>
                            </tr>
                            <?php }
                                }
                            } else {
                                echo '<tr><td colspan="6">Unable to load payments</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </section>

                </div> <!-- dashboard-content-main -->

                <div class="dashboard-content-sidebar">
                    <?php echo displayNewsHTML($news, 5); ?>
                </div>
            </div> <!-- dashboard-content -->
        </main>
    </div>

    <script src="../../../assets/js/main.js"></script>
</body>
</html>
