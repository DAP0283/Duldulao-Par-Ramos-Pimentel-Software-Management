<?php
session_start();
// Set timezone to GMT +8
date_default_timezone_set('Asia/Manila');

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff' || $_SESSION['role'] !== 'Barangay Treasurer') {
    header('Location: ../../../auth/staff-login.php');
    exit();
}

require_once('../../../includes/db_config.php');
require_once('../../../includes/auth_functions.php');

$staff_name = $_SESSION['name'];
$user_id = $_SESSION['user_id'];

// 2. MSSQL Record Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];

    // Using GETDATE() in MSSQL will use the Server's time. 
    // If your SQL server is on a different timezone, we can pass the PHP time instead.
    $sql = "INSERT INTO transactions (transaction_type, amount, description, created_by, status, created_at) 
            VALUES (?, ?, ?, ?, 'Completed', GETDATE())";
    
    $params = array($type, $amount, $description, $user_id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    } else {
        header("Location: transactions.php?success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Barangay e-Services</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <style>
        @media print {
            .sidebar, .top-navbar, .form-container, .print-btn, .success-msg, hr {
                display: none !important;
            }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            .dashboard-content { padding: 0 !important; }
            .staff-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            .staff-table th, .staff-table td { border: 1px solid #000 !important; padding: 10px; text-align: left; }
            .print-header { display: block !important; text-align: center; margin-bottom: 30px; }
        }
        .print-header { display: none; border-bottom: 2px solid #333; padding-bottom: 10px; }
    </style>
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
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="transactions.php" class="active">Transactions</a></li>
                    <li><a href="financial-reports.php">Financial Reports</a></li>
                    <li><a href="budget-management.php">Budget Management</a></li>
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="print-header">
                <h1 style="margin:0;">BARANGAY E-SERVICES</h1>
                <h2 style="margin:5px 0;">Transaction History Report</h2>
                <p><strong>Staff:</strong> <?php echo $staff_name; ?> | <strong>Generated:</strong> <?php echo date('F d, Y - h:i A'); ?> (GMT+8)</p>
            </div>

            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Manage Transactions</h2>
                </div>
            </header>

            <div class="dashboard-content">
                <section class="staff-section">
                    <h3>Financial Entry Form</h3>
                    
                    <?php if(isset($_GET['success'])): ?>
                        <div class="success-msg" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                            ✔ Transaction recorded successfully!
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="form-container" style="margin-bottom: 30px; background: #f9f9f9; padding: 25px; border-radius: 8px; border: 1px solid #eee;">
                        <div class="form-group">
                            <label>Transaction Type</label>
                            <select name="type" class="form-control" required>
                                <option value="Income">Income</option>
                                <option value="Expense">Expense</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Amount (PHP)</label>
                            <input type="number" name="amount" class="form-control" placeholder="0.00" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Record Transaction</button>
                    </form>

                    <hr>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3>Recent Transactions</h3>
                        <button onclick="window.print()" class="print-btn" style="background: #34495e; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">
                            Print Report
                        </button>
                    </div>

                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $fetch_sql = "SELECT * FROM transactions ORDER BY created_at DESC";
                            $query = sqlsrv_query($conn, $fetch_sql);
                            $hasRows = false;
                            while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
                                $hasRows = true;
                                // Display date in GMT+8 format
                                $date = $row['created_at']->format('M d, Y');
                                $color = ($row['transaction_type'] == 'Income') ? '#2ecc71' : '#e74c3c';
                                ?>
                                <tr>
                                    <td><?php echo $date; ?></td>
                                    <td><b style="color: <?php echo $color; ?>;"><?php echo $row['transaction_type']; ?></b></td>
                                    <td>₱<?php echo number_format($row['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                </tr>
                                <?php
                            }
                            if (!$hasRows) {
                                echo '<tr><td colspan="4" style="text-align:center; padding:20px;">No records found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </div>
</body>
</html>