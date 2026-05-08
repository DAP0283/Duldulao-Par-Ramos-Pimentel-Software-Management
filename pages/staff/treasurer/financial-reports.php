<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff' || $_SESSION['role'] !== 'Barangay Treasurer') {
    header('Location: ../../../auth/staff-login.php');
    exit();
}
require_once('../../../includes/db_config.php');
require_once('../../../includes/db_functions_enhanced.php');

// Determine timeframe from POST/GET
$report_error = '';
$transactions = array();
$preset = $_GET['preset'] ?? $_POST['preset'] ?? '';
$start_date = $_GET['start_date'] ?? $_POST['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? $_POST['end_date'] ?? '';

// Compute start/end based on preset
if ($preset) {
    $today = new DateTime();
    if ($preset === 'monthly') {
        $start_date = $today->format('Y-m-01');
        $end_date = $today->format('Y-m-t');
    } elseif ($preset === 'quarterly') {
        $month = (int)$today->format('n');
        $quarter = (int)ceil($month / 3);
        $start_month = ($quarter - 1) * 3 + 1;
        $start = new DateTime($today->format('Y') . '-' . str_pad($start_month,2,'0',STR_PAD_LEFT) . '-01');
        $end = clone $start;
        $end->modify('+2 months')->modify('last day of this month');
        $start_date = $start->format('Y-m-d');
        $end_date = $end->format('Y-m-d');
    } elseif ($preset === 'annual') {
        $start_date = $today->format('Y-01-01');
        $end_date = $today->format('Y-12-31');
    }
}

// If start/end provided, fetch transactions
if (!empty($start_date) && !empty($end_date)) {
    $transactions = getPaymentsByDateRange($start_date, $end_date);
    if (!is_array($transactions)) $transactions = array();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Reports - Barangay e-Services</title>
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
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="transactions.php">Transactions</a></li>
                    <li><a href="financial-reports.php" class="active">Financial Reports</a></li>
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
                    <h2>Financial Reports</h2>
                </div>
            </header>
            <div class="dashboard-content">
                <section class="staff-section">
                    <h3>Generate Reports</h3>
                    <div class="report-form">
                        <form method="get" action="financial-reports.php">
                            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                                <input type="hidden" name="preset" id="preset_input" value="<?php echo htmlspecialchars($preset); ?>">
                                <button type="button" class="btn btn-primary" onclick="applyPreset('monthly')">Monthly Report</button>
                                <button type="button" class="btn btn-primary" onclick="applyPreset('quarterly')">Quarterly Report</button>
                                <button type="button" class="btn btn-primary" onclick="applyPreset('annual')">Annual Report</button>
                                <label style="margin-left:8px;">Or custom range:</label>
                                <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                                <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                                <button type="submit" class="btn btn-primary">Generate</button>
                            </div>
                        </form>
                    </div>
                    <script>
                        function applyPreset(p) {
                            document.getElementById('preset_input').value = p;
                            document.forms[0].submit();
                        }
                    </script>
                </section>

                <?php if (!empty($transactions)): ?>
                <section class="staff-section">
                    <h3>Transactions from <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?></h3>
                    <div style="margin-bottom:8px;">
                        <button type="button" class="btn btn-secondary" onclick="printTransactions()">Print Report</button>
                    </div>
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Application</th>
                                <th>Client</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Transaction</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($transactions as $t):
                            $created = $t['CreatedAt'];
                            $date = ($created instanceof DateTime) ? $created->format('Y-m-d') : date('Y-m-d', strtotime($created));
                        ?>
                            <tr>
                                <td>#PAY-<?php echo str_pad($t['PaymentID'],4,'0',STR_PAD_LEFT); ?></td>
                                <td><a href="../view-application.php?id=<?php echo urlencode($t['ApplicationID']); ?>">#APP-<?php echo str_pad($t['ApplicationID'],5,'0',STR_PAD_LEFT); ?></a></td>
                                <td><?php echo htmlspecialchars($t['ClientID'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($t['Method']); ?></td>
                                <td>₱<?php echo number_format($t['Amount'],2); ?></td>
                                <td><?php echo htmlspecialchars($t['TransactionID']); ?></td>
                                <td><?php echo htmlspecialchars($date); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
                <?php elseif (!empty($start_date) || !empty($end_date)): ?>
                <section class="staff-section">
                    <p>No transactions found for the selected timeframe.</p>
                </section>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="../../../assets/js/main.js"></script>
    <script>
        function printTransactions() {
            var title = document.querySelector('h3').innerText || 'Financial Report';
            var table = document.querySelector('.staff-table');
            if (!table) {
                alert('No transactions to print.');
                return;
            }

            var win = window.open('', '_blank');
            var style = `
                <style>
                    body { font-family: Arial, Helvetica, sans-serif; padding:20px; }
                    h1 { font-size:18px; }
                    table { border-collapse: collapse; width: 100%; }
                    table, th, td { border: 1px solid #ddd; }
                    th, td { padding: 8px; text-align: left; }
                    th { background: #f4f6f8; }
                </style>
            `;

            win.document.write('<!doctype html><html><head><meta charset="utf-8"><title>' + title + '</title>' + style + '</head><body>');
            win.document.write('<h1>' + title + '</h1>');
            win.document.write(table.outerHTML);
            win.document.write('</body></html>');
            win.document.close();
            // Give the new window a moment to render
            setTimeout(function() {
                win.focus();
                win.print();
                // Optionally close after printing
                // win.close();
            }, 250);
        }
    </script>
</body>
</html>
