<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff' || $_SESSION['role'] !== 'Punong Barangay') {
    header('Location: ../../../auth/staff-login.php');
    exit();
}
require_once('../../../includes/db_config.php');
$staff_name = $_SESSION['name'];
$staff_id = $_SESSION['user_id'];

$success_message = '';
$error_message = '';
$tab = $_GET['tab'] ?? 'inbox';

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $subject = $_POST['subject'] ?? '';
    $message_body = $_POST['message_body'] ?? '';
    $send_to_all = isset($_POST['send_to_all']) ? 1 : 0;
    $recipient_id = $_POST['recipient_id'] ?? null;
    
    if (empty($subject) || empty($message_body)) {
        $error_message = 'Subject and message body are required';
    } else {
        if ($send_to_all) {
            $staff_query = "SELECT StaffID FROM Staff WHERE StaffID != ? AND IsActive = 1";
            $staff_stmt = sqlsrv_query($conn, $staff_query, array($staff_id));
            
            if ($staff_stmt !== false) {
                $message_type = 'Broadcast';
                while ($staff_row = sqlsrv_fetch_array($staff_stmt, SQLSRV_FETCH_ASSOC)) {
                    $recipient = $staff_row['StaffID'];
                    $insert_query = "INSERT INTO Messages (SenderID, RecipientID, MessageType, Subject, MessageBody, CreatedAt) 
                                     VALUES (?, ?, ?, ?, ?, GETDATE())";
                    $insert_params = array($staff_id, $recipient, $message_type, $subject, $message_body);
                    sqlsrv_query($conn, $insert_query, $insert_params);
                }
                $success_message = 'Message sent to all staff members';
            }
        } else if ($recipient_id) {
            $message_type = 'Personal';
            $insert_query = "INSERT INTO Messages (SenderID, RecipientID, MessageType, Subject, MessageBody, CreatedAt) 
                             VALUES (?, ?, ?, ?, ?, GETDATE())";
            $insert_params = array($staff_id, intval($recipient_id), $message_type, $subject, $message_body);
            if (sqlsrv_query($conn, $insert_query, $insert_params) !== false) {
                $success_message = 'Message sent successfully';
            } else {
                $error_message = 'Failed to send message';
            }
        } else {
            $error_message = 'Please select a recipient or choose to send to all';
        }
    }
}

// Fetch inbox messages
$messages = array();
$inbox_query = "SELECT m.MessageID, m.SenderID, m.Subject, m.MessageBody, m.CreatedAt, m.IsRead, m.MessageType,
                       s.FirstName, s.LastName
                FROM Messages m
                LEFT JOIN Staff s ON m.SenderID = s.StaffID
                WHERE m.RecipientID = ? OR (m.MessageType = 'Broadcast' AND m.SenderID != ?)
                ORDER BY m.CreatedAt DESC";
$inbox_stmt = sqlsrv_query($conn, $inbox_query, array($staff_id, $staff_id));

if ($inbox_stmt !== false) {
    while ($row = sqlsrv_fetch_array($inbox_stmt, SQLSRV_FETCH_ASSOC)) {
        $created = $row['CreatedAt'];
        if ($created instanceof DateTime) {
            $date_formatted = $created->format('Y-m-d H:i');
        } else {
            $date_formatted = date('Y-m-d H:i', strtotime($created));
        }
        
        $messages[] = array(
            'id' => $row['MessageID'],
            'from' => ($row['FirstName'] ?? 'System') . ' ' . ($row['LastName'] ?? ''),
            'subject' => $row['Subject'] ?? 'No Subject',
            'body' => $row['MessageBody'] ?? '',
            'date' => $date_formatted,
            'is_read' => $row['IsRead'],
            'type' => $row['MessageType'] ?? 'Personal'
        );
    }
}

// Fetch all staff for recipient dropdown
$staff_list = array();
$staff_query = "SELECT StaffID, FirstName, LastName, Role FROM Staff WHERE StaffID != ? AND IsActive = 1 ORDER BY FirstName";
$staff_stmt = sqlsrv_query($conn, $staff_query, array($staff_id));

if ($staff_stmt !== false) {
    while ($staff_row = sqlsrv_fetch_array($staff_stmt, SQLSRV_FETCH_ASSOC)) {
        $staff_list[] = array(
            'id' => $staff_row['StaffID'],
            'name' => ($staff_row['FirstName'] ?? '') . ' ' . ($staff_row['LastName'] ?? '') . ' (' . ($staff_row['Role'] ?? '') . ')'
        );
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Barangay e-Services</title>
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
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="messages.php" class="active">Messages</a></li>
                    <li><a href="../../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Messages</h2>
                </div>
            </header>
            <div class="dashboard-content">
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <div style="margin-bottom: 20px; display: flex; gap: 10px; border-bottom: 2px solid #ddd; padding-bottom: 10px;">
                    <a href="?tab=inbox" class="btn <?php echo $tab === 'inbox' ? 'btn-primary' : 'btn-secondary'; ?>" style="text-decoration: none;">Inbox</a>
                    <a href="?tab=compose" class="btn <?php echo $tab === 'compose' ? 'btn-primary' : 'btn-secondary'; ?>" style="text-decoration: none;">Compose Message</a>
                </div>

                <?php if ($tab === 'compose'): ?>
                    <section class="staff-section">
                        <h3>Send Message</h3>
                        <form method="POST" class="form-container">
                            <div class="form-group">
                                <label for="subject">Subject:</label>
                                <input type="text" id="subject" name="subject" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="recipient_id">Send To:</label>
                                <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                                    <label style="display: flex; align-items: center; gap: 5px;">
                                        <input type="checkbox" id="send_to_all" name="send_to_all" onchange="toggleRecipient()">
                                        Send to All Staff
                                    </label>
                                </div>
                                <select id="recipient_id" name="recipient_id" class="form-control" required>
                                    <option value="">-- Select Staff Member --</option>
                                    <?php foreach ($staff_list as $staff): ?>
                                        <option value="<?php echo htmlspecialchars($staff['id']); ?>">
                                            <?php echo htmlspecialchars($staff['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="message_body">Message:</label>
                                <textarea id="message_body" name="message_body" class="form-control" rows="6" required></textarea>
                            </div>

                            <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
                        </form>
                    </section>
                <?php else: ?>
                    <section class="staff-section">
                        <h3>Messages & Communications</h3>
                        <table class="staff-table">
                            <thead>
                                <tr>
                                    <th>From</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($messages)): ?>
                                    <?php foreach ($messages as $msg): ?>
                                    <tr style="<?php echo !$msg['is_read'] ? 'font-weight: bold; background-color: #f0f8ff;' : ''; ?>">
                                        <td><?php echo htmlspecialchars($msg['from']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['date']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $msg['type'] === 'Broadcast' ? 'badge-info' : 'badge-secondary'; ?>">
                                                <?php echo htmlspecialchars($msg['type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button onclick="viewMessage(<?php echo htmlspecialchars($msg['id']); ?>)" class="btn btn-xs btn-primary">View</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5">No messages</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </section>
                <?php endif; ?>
            </div>
            </div>
        </main>
    </div>
    <script src="../../../assets/js/main.js"></script>
    <script>
        function toggleRecipient() {
            const sendToAll = document.getElementById('send_to_all').checked;
            const recipientSelect = document.getElementById('recipient_id');
            recipientSelect.disabled = sendToAll;
            recipientSelect.required = !sendToAll;
        }

        function viewMessage(messageId) {
            alert('Message details:\n\nFull message viewing feature coming soon.\n\nMessage ID: ' + messageId);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const sendToAll = document.getElementById('send_to_all').checked;
                    const recipientId = document.getElementById('recipient_id').value;
                    
                    if (!sendToAll && !recipientId) {
                        e.preventDefault();
                        alert('Please select a recipient or choose to send to all staff');
                    }
                });
            }
        });
    </script>
</body>
</html>
