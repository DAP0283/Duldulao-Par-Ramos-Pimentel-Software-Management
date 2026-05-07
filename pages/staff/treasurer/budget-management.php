<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff' || $_SESSION['role'] !== 'Barangay Treasurer') {
    header('Location: ../../../auth/staff-login.php');
    exit();
}
require_once('../../../includes/db_config.php');

// --- DATABASE UPDATE LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_budget'])) {
    $id = $_POST['budget_id'];
    $category = $_POST['category'];
    $allocated = $_POST['allocated_amount'];
    $spent = $_POST['spent_amount'];

    $sql = "UPDATE budget_allocations SET category = ?, allocated_amount = ?, spent_amount = ? WHERE id = ?";
    $params = array($category, $allocated, $spent, $id);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    // Refresh to show changes
    header("Location: budget-management.php?success=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Management - Barangay e-Services</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <style>
        /* Simple Modal Styling */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 10% auto; padding: 20px; width: 400px; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 8px; box-sizing: border-box; }
        .btn-save { background: #28a745; color: white; border: none; padding: 10px 15px; cursor: pointer; }
        .btn-cancel { background: #6c757d; color: white; border: none; padding: 10px 15px; cursor: pointer; }
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
                    <li><a href="transactions.php">Transactions</a></li>
                    <li><a href="financial-reports.php">Financial Reports</a></li>
                    <li><a href="budget-management.php" class="active">Budget Management</a></li>
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Budget Management</h2>
                </div>
            </header>

            <div class="dashboard-content">
                <section class="staff-section">
                    <h3>Budget Allocations</h3>
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Allocated</th>
                                <th>Spent</th>
                                <th>Remaining</th>
                                <th>%</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT id, category, allocated_amount, spent_amount FROM budget_allocations";
                            $stmt = sqlsrv_query($conn, $sql);
                            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                $remaining = $row['allocated_amount'] - $row['spent_amount'];
                                $percent = ($row['allocated_amount'] > 0) ? ($row['spent_amount'] / $row['allocated_amount']) * 100 : 0;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td>₱<?php echo number_format($row['allocated_amount'], 2); ?></td>
                                    <td>₱<?php echo number_format($row['spent_amount'], 2); ?></td>
                                    <td>₱<?php echo number_format($remaining, 2); ?></td>
                                    <td><?php echo number_format($percent, 1); ?>%</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" 
                                            onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo $row['category']; ?>', <?php echo $row['allocated_amount']; ?>, <?php echo $row['spent_amount']; ?>)">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Edit Budget</h3>
            <form method="POST">
                <input type="hidden" name="budget_id" id="modal_id">
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" id="modal_category" required>
                </div>
                <div class="form-group">
                    <label>Allocated Amount (₱)</label>
                    <input type="number" step="0.01" name="allocated_amount" id="modal_allocated" required>
                </div>
                <div class="form-group">
                    <label>Spent Amount (₱)</label>
                    <input type="number" step="0.01" name="spent_amount" id="modal_spent" required>
                </div>
                <button type="submit" name="update_budget" class="btn-save">Save Changes</button>
                <button type="button" onclick="closeModal()" class="btn-cancel">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, category, allocated, spent) {
            document.getElementById('modal_id').value = id;
            document.getElementById('modal_category').value = category;
            document.getElementById('modal_allocated').value = allocated;
            document.getElementById('modal_spent').value = spent;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</body>
</html>