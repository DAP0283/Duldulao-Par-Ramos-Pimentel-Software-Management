<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: ../../../auth/staff-login.php');
    exit();
}
$staff_role = $_SESSION['role'] ?? '';
$staff_id = $_SESSION['user_id'];
$staff_name = $_SESSION['name'] ?? 'Staff';
require_once('../../../includes/db_config.php');

$success_message = '';
$error_message = '';
$tab = $_GET['tab'] ?? 'inbox';

// Handle viewing message and marking as read
if (isset($_GET['view_message_id'])) {
    $message_id = intval($_GET['view_message_id']);
    $view_query = "SELECT m.MessageID, m.SenderID, m.Subject, m.MessageBody, m.CreatedAt, m.IsRead, m.MessageType,
                         s.FirstName, s.LastName, s.Role
                  FROM Messages m
                  LEFT JOIN Staff s ON m.SenderID = s.StaffID
                  WHERE m.MessageID = ? AND (m.RecipientID = ? OR m.SenderID = ?)";
    $view_stmt = sqlsrv_query($conn, $view_query, array($message_id, $staff_id, $staff_id));
    
    if ($view_stmt !== false && $row = sqlsrv_fetch_array($view_stmt, SQLSRV_FETCH_ASSOC)) {
        // Mark as read
        $update_query = "UPDATE Messages SET IsRead = 1 WHERE MessageID = ?";
        sqlsrv_query($conn, $update_query, array($message_id));
        
        header('Content-Type: application/json');
        $created = $row['CreatedAt'];
        if ($created instanceof DateTime) {
            $date_formatted = $created->format('Y-m-d H:i:s');
        } else {
            $date_formatted = date('Y-m-d H:i:s', strtotime($created));
        }
        
        echo json_encode(array(
            'id' => $row['MessageID'],
            'from' => ($row['FirstName'] ?? 'System') . ' ' . ($row['LastName'] ?? '') . ' (' . ($row['Role'] ?? 'Staff') . ')',
            'subject' => $row['Subject'] ?? 'No Subject',
            'body' => $row['MessageBody'] ?? '',
            'date' => $date_formatted,
            'type' => $row['MessageType'] ?? 'Personal'
        ));
        exit();
    }
}

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
            // Get all other staff members
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
            // Send to specific recipient
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
                <h3>Secretary Portal</h3>
                <p class="role-badge"><?php echo htmlspecialchars($staff_role); ?></p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <?php if ($staff_role === 'Punong Barangay'): ?>
                    <li><a href="../punong-barangay/dashboard.php">Dashboard</a></li>
                    <li><a href="../punong-barangay/staff-management.php">Staff Management</a></li>
                    <li><a href="../punong-barangay/applications.php">Applications</a></li>
                    <?php elseif ($staff_role === 'Barangay Treasurer'): ?>
                    <li><a href="../treasurer/dashboard.php">Dashboard</a></li>
                    <li><a href="../treasurer/transactions.php">Transactions</a></li>
                    <li><a href="../treasurer/budget-management.php">Budget</a></li>
                    <?php else: ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="clients.php">Clients</a></li>
                    <?php endif; ?>
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
        </main>
    </div>
    <script src="../../../assets/js/main.js"></script>
    
    <!-- Message Modal -->
    <div id="messageModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div class="modal-content" style="background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 700px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <span class="close" onclick="closeMessageModal()" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
            <h3 id="modalSubject"></h3>
            <div style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #ddd;">
                <p style="margin: 5px 0;"><strong>From:</strong> <span id="modalFrom"></span></p>
                <p style="margin: 5px 0;"><strong>Type:</strong> <span id="modalType"></span></p>
                <p style="margin: 5px 0;"><strong>Date:</strong> <span id="modalDate"></span></p>
            </div>
            <div style="background-color: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 15px; min-height: 100px; max-height: 400px; overflow-y: auto;">
                <p id="modalBody" style="margin: 0; white-space: pre-wrap; word-wrap: break-word;"></p>
            </div>
            <div style="text-align: right;">
                <button onclick="closeMessageModal()" class="btn btn-secondary">Close</button>
            </div>
        </div>
    </div>
    
    <script>
        function toggleRecipient() {
            const sendToAll = document.getElementById('send_to_all').checked;
            const recipientSelect = document.getElementById('recipient_id');
            recipientSelect.disabled = sendToAll;
            recipientSelect.required = !sendToAll;
        }

        function viewMessage(messageId) {
            fetch('?view_message_id=' + messageId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalSubject').textContent = data.subject;
                    document.getElementById('modalFrom').textContent = data.from;
                    document.getElementById('modalBody').textContent = data.body;
                    document.getElementById('modalDate').textContent = data.date;
                    document.getElementById('modalType').innerHTML = '<span class="badge ' + 
                        (data.type === 'Broadcast' ? 'badge-info' : 'badge-secondary') + '">' + 
                        data.type + '</span>';
                    document.getElementById('messageModal').style.display = 'block';
                })
                .catch(error => {
                    alert('Error loading message: ' + error);
                });
        }

        function closeMessageModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('messageModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        // Form validation
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
</html>
