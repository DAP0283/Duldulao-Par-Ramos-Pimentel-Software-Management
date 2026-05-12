<?php
session_start();
// Set timezone to GMT +8
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff' || $_SESSION['role'] !== 'Barangay Treasurer') {
    header('Location: ../../../auth/staff-login.php');
    exit();
}

require_once('../../../includes/db_config.php');
// Ensure this file is updated for MSSQL (sqlsrv)
require_once('../../../includes/db_functions_enhanced.php');

$staff_name = $_SESSION['name'];

$transactions = array();
$preset = $_GET['preset'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// --- LOGIC FIX: Compute dates ONLY if a preset is explicitly requested ---
if (!empty($preset)) {
    $today = new DateTime();
    if ($preset === 'monthly') {
        $start_date = $today->format('Y-m-01');
        $end_date = $today->format('Y-m-t');
    } elseif ($preset === 'quarterly') {
        $month = (int)$today->format('n');
        $quarter = (int)ceil($month / 3);
        $start_month = ($quarter - 1) * 3 + 1;
        $start = new DateTime($today->format('Y') . '-' . str_pad($start_month, 2, '0', STR_PAD_LEFT) . '-01');
        $end = clone $start;
        $end->modify('+2 months')->modify('last day of this month');
        $start_date = $start->format('Y-m-d');
        $end_date = $end->format('Y-m-d');
    } elseif ($preset === 'annual') {
        $start_date = $today->format('Y-01-01');
        $end_date = $today->format('Y-12-31');
    }
}

// Fetch transactions if dates are set (either by preset or custom range)
if (!empty($start_date) && !empty($end_date)) {
    // NOTE: Ensure getPaymentsByDateRange() in db_functions_enhanced.php uses sqlsrv_query
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
                        <form method="get" action="financial-reports.php" id="reportForm">
                            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                                <input type="hidden" name="preset" id="preset_input" value="<?php echo htmlspecialchars($preset); ?>">
                                <button type="button" class="btn btn-primary" onclick="applyPreset('monthly')">Monthly</button>
                                <button type="button" class="btn btn-primary" onclick="applyPreset('quarterly')">Quarterly</button>
                                <button type="button" class="btn btn-primary" onclick="applyPreset('annual')">Annual</button>
                                
                                <span style="margin: 0 10px; color: #ccc;">|</span>
                                
                                <label>Custom Range:</label>
                                <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                                <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                                
                                <button type="submit" class="btn btn-primary" onclick="clearPreset()">Generate</button>
                            </div>
                        </form>
                    </div>
                </section>

                <?php if (!empty($transactions)): ?>
                <section class="staff-section">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3>Transactions: <?php echo date('M d, Y', strtotime($start_date)); ?> to <?php echo date('M d, Y', strtotime($end_date)); ?></h3>
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
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($transactions as $t): 
                            $created = $t['CreatedAt'];
                            $dateStr = ($created instanceof DateTime) ? $created->format('Y-m-d') : date('Y-m-d', strtotime($created));
                        ?>
                            <tr>
                                <td>#PAY-<?php echo str_pad($t['PaymentID'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td>#APP-<?php echo str_pad($t['ApplicationID'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($t['ClientID'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($t['Method']); ?></td>
                                <td>₱<?php echo number_format($t['Amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($dateStr); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
                <?php elseif (!empty($start_date)): ?>
                <section class="staff-section">
                    <p style="text-align: center; color: #666;">No transactions found for the selected period.</p>
                </section>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Use a preset and submit
        function applyPreset(p) {
            document.getElementById('preset_input').value = p;
            document.getElementById('reportForm').submit();
        }

        // IMPORTANT: Clear the preset value if the user clicks "Generate" for a custom range
        function clearPreset() {
            document.getElementById('preset_input').value = '';
        }

        function printTransactions() {
            var title = "Financial Report (<?php echo $start_date; ?> to <?php echo $end_date; ?>)";
            var table = document.querySelector('.staff-table');
            if (!table) return;

            var win = window.open('', '_blank');
            var style = `
                <style>
                    body { font-family: sans-serif; padding: 40px; }
                    h1 { text-align: center; font-size: 22px; }
                    p { text-align: center; color: #555; }
                    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
                    th, td { border: 1px solid #000; padding: 10px; text-align: left; }
                    th { background: #eee; }
                </style>
            `;

            win.document.write('<html><head><title>Print Report</title>' + style + '</head><body>');
            win.document.write('<h1>Barangay e-Services Financial Report</h1>');
            win.document.write('<p>' + title + '<br>Generated on: <?php echo date("F d, Y h:i A"); ?></p>');
            win.document.write(table.outerHTML);
            win.document.write('</body></html>');
            win.document.close();
            
            setTimeout(function() {
                win.focus();
                win.print();
            }, 500);
        }
    </script>
</body>
</html>