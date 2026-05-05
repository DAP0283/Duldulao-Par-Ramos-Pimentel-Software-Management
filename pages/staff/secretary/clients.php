<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff' || $_SESSION['role'] !== 'Barangay Secretary') {
    header('Location: ../../../auth/staff-login.php');
    exit();
}
require_once('../../../includes/db_config.php');

// Fetch all clients from database
$clients = array();
$query = "SELECT ClientID, FirstName, LastName, Email, PhoneNumber, CreatedAt FROM Clients ORDER BY CreatedAt DESC";
$stmt = sqlsrv_query($conn, $query);

if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $created = $row['CreatedAt'];
        if ($created instanceof DateTime) {
            $date_joined = $created->format('Y-m-d');
        } else {
            $date_joined = date('Y-m-d', strtotime($created));
        }
        
        $clients[] = array(
            'id' => $row['ClientID'],
            'name' => ($row['FirstName'] ?? 'Unknown') . ' ' . ($row['LastName'] ?? ''),
            'email' => $row['Email'] ?? 'N/A',
            'phone' => $row['PhoneNumber'] ?? 'N/A',
            'joined' => $date_joined
        );
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients - Barangay e-Services</title>
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
                    <li><a href="clients.php" class="active">Clients</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Client Management</h2>
                </div>
            </header>
            <div class="dashboard-content">
                <section class="staff-section">
                    <h3>Client Records</h3>
                    <input type="text" class="form-control" placeholder="Search clients..." style="margin-bottom: 15px;">
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Joined</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($clients)): ?>
                                <?php foreach ($clients as $client): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($client['name']); ?></td>
                                    <td><?php echo htmlspecialchars($client['email']); ?></td>
                                    <td><?php echo htmlspecialchars($client['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($client['joined']); ?></td>
                                    <td>
                                        <a href="view-client.php?id=<?php echo urlencode($client['id']); ?>" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5">No clients found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
            </div>
        </main>
    </div>
    <script src="../../../assets/js/main.js"></script>
</body>
</html>
