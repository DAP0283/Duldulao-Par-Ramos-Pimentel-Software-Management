<?php
/**
 * Staff Management - Punong Barangay
 * Manage staff members, roles, and permissions
 */
session_start();

// Validate staff session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
    header('Location: ../../../auth/staff-login.php');
    exit();
}

// Verify user is Punong Barangay
$staff_role = $_SESSION['role'] ?? '';
if ($staff_role !== 'Punong Barangay') {
    header('Location: ../dashboard.php');
    exit();
}

// Include database functions
require_once('../../../includes/db_config.php');
require_once('../../../includes/auth_functions.php');

// Get staff information from session
$staff_id = $_SESSION['user_id'];
$staff_name = $_SESSION['name'];

// Get all staff members
$staff_query = "SELECT StaffID, FirstName, LastName, Email, Position, Role, Department, IsActive, CreatedAt FROM Staff ORDER BY FirstName, LastName";
$staff_result = sqlsrv_query($conn, $staff_query);
$staff_members = array();

if ($staff_result !== false) {
    while ($row = sqlsrv_fetch_array($staff_result, SQLSRV_FETCH_ASSOC)) {
        $staff_members[] = $row;
    }
}

// Get available roles for dropdown
$available_roles = array(
    'Barangay Secretary',
    'Barangay Treasurer',
    'Sanggunian Member'
);

// Get available positions for dropdown
$available_positions = array(
    'Clerk',
    'Revenue Officer',
    'Health Worker',
    'Maintenance Staff',
    'Security'
);

// Get available departments for dropdown
$available_departments = array(
    'Administration',
    'Finance',
    'Health & Welfare',
    'Internal Services',
    'General Services'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - Barangay e-Services</title>
    <link rel="stylesheet" href="../../../assets/css/style.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fefefe;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 15px;
        }

        .modal-header h3 {
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6b7280;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #374151;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .staff-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background-color: #fee2e2;
            color: #7f1d1d;
        }

        .action-cell {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .action-cell .btn {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Barangay Executive</h3>
                <p class="role-badge">Punong Barangay</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="staff-management.php" class="active">Staff Management</a></li>
                    <li><a href="staff-roles.php">Roles & Permissions</a></li>
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="../../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Staff Management</h2>
                    <div class="user-info">
                        <a href="../../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <!-- Staff Management Section -->
                <section class="staff-section">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3>Staff Members</h3>
                        <button class="btn btn-primary" onclick="openAddStaffModal()">+ Add New Staff</button>
                    </div>

                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Position</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($staff_members as $staff): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($staff['FirstName'] . ' ' . $staff['LastName']); ?></td>
                                <td><?php echo htmlspecialchars($staff['Email']); ?></td>
                                <td><?php echo htmlspecialchars($staff['Position'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($staff['Role'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($staff['Department'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="staff-status <?php echo $staff['IsActive'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $staff['IsActive'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="action-cell">
                                    <button class="btn btn-xs btn-info" onclick="openEditStaffModal(<?php echo htmlspecialchars(json_encode($staff)); ?>)">Edit</button>
                                    <button class="btn btn-xs btn-warning" onclick="openRoleModal(<?php echo $staff['StaffID']; ?>, '<?php echo htmlspecialchars($staff['FirstName'] . ' ' . $staff['LastName']); ?>', '<?php echo htmlspecialchars($staff['Role']); ?>')">Change Role</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>

                <!-- Staff Summary -->
                <section class="staff-section">
                    <h3>Staff Summary</h3>
                    <p>Total active staff: <strong><?php echo count(array_filter($staff_members, function($s) { return $s['IsActive']; })); ?></strong></p>
                    <p>Total inactive staff: <strong><?php echo count(array_filter($staff_members, function($s) { return !$s['IsActive']; })); ?></strong></p>
                </section>
            </div>
        </main>
    </div>

    <!-- Modal for Adding New Staff -->
    <div id="addStaffModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Staff Member</h3>
                <button class="close-modal" onclick="closeAddStaffModal()">&times;</button>
            </div>
            <form method="POST" onsubmit="handleAddStaff(event)">
                <div class="form-group">
                    <label for="first_name">First Name <span class="required">*</span></label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name <span class="required">*</span></label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="position">Position <span class="required">*</span></label>
                    <select id="position" name="position" required>
                        <option value="">Select Position</option>
                        <?php foreach ($available_positions as $pos): ?>
                        <option value="<?php echo htmlspecialchars($pos); ?>"><?php echo htmlspecialchars($pos); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="role">Role <span class="required">*</span></label>
                    <select id="role" name="role" required>
                        <option value="">Select Role</option>
                        <?php foreach ($available_roles as $r): ?>
                        <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="department">Department <span class="required">*</span></label>
                    <select id="department" name="department" required>
                        <option value="">Select Department</option>
                        <?php foreach ($available_departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Add Staff Member</button>
                    <button type="button" class="btn btn-secondary" onclick="closeAddStaffModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Editing Staff -->
    <div id="editStaffModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Staff Member</h3>
                <button class="close-modal" onclick="closeEditStaffModal()">&times;</button>
            </div>
            <form method="POST" onsubmit="handleEditStaff(event)">
                <input type="hidden" id="edit_staff_id" name="staff_id">
                <div class="form-group">
                    <label for="edit_first_name">First Name</label>
                    <input type="text" id="edit_first_name" name="first_name">
                </div>
                <div class="form-group">
                    <label for="edit_last_name">Last Name</label>
                    <input type="text" id="edit_last_name" name="last_name">
                </div>
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email">
                </div>
                <div class="form-group">
                    <label for="edit_position">Position</label>
                    <select id="edit_position" name="position">
                        <option value="">Select Position</option>
                        <?php foreach ($available_positions as $pos): ?>
                        <option value="<?php echo htmlspecialchars($pos); ?>"><?php echo htmlspecialchars($pos); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_department">Department</label>
                    <select id="edit_department" name="department">
                        <option value="">Select Department</option>
                        <?php foreach ($available_departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditStaffModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Changing Role -->
    <div id="roleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Change Staff Role</h3>
                <button class="close-modal" onclick="closeRoleModal()">&times;</button>
            </div>
            <form method="POST" onsubmit="handleChangeRole(event)">
                <input type="hidden" id="role_staff_id" name="staff_id">
                <div class="form-group">
                    <label>Staff Member: <span id="role_staff_name"></span></label>
                </div>
                <div class="form-group">
                    <label for="new_role">New Role <span class="required">*</span></label>
                    <select id="new_role" name="role" required>
                        <option value="">Select Role</option>
                        <?php foreach ($available_roles as $r): ?>
                        <option value="<?php echo htmlspecialchars($r); ?>"><?php echo htmlspecialchars($r); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="role_notes">Notes</label>
                    <input type="text" id="role_notes" name="notes" placeholder="Reason for role change">
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Change Role</button>
                    <button type="button" class="btn btn-secondary" onclick="closeRoleModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddStaffModal() {
            document.getElementById('addStaffModal').classList.add('show');
        }

        function closeAddStaffModal() {
            document.getElementById('addStaffModal').classList.remove('show');
            document.querySelector('#addStaffModal form').reset();
        }

        function handleAddStaff(event) {
            event.preventDefault();
            alert('Staff member addition feature coming soon!');
        }

        function openEditStaffModal(staffData) {
            document.getElementById('edit_staff_id').value = staffData.StaffID;
            document.getElementById('edit_first_name').value = staffData.FirstName;
            document.getElementById('edit_last_name').value = staffData.LastName;
            document.getElementById('edit_email').value = staffData.Email;
            document.getElementById('edit_position').value = staffData.Position || '';
            document.getElementById('edit_department').value = staffData.Department || '';
            document.getElementById('editStaffModal').classList.add('show');
        }

        function closeEditStaffModal() {
            document.getElementById('editStaffModal').classList.remove('show');
        }

        function handleEditStaff(event) {
            event.preventDefault();
            alert('Staff member edit feature coming soon!');
        }

        function openRoleModal(staffId, staffName, currentRole) {
            document.getElementById('role_staff_id').value = staffId;
            document.getElementById('role_staff_name').textContent = staffName + ' (Current: ' + currentRole + ')';
            document.getElementById('new_role').value = '';
            document.getElementById('role_notes').value = '';
            document.getElementById('roleModal').classList.add('show');
        }

        function closeRoleModal() {
            document.getElementById('roleModal').classList.remove('show');
        }

        function handleChangeRole(event) {
            event.preventDefault();
            alert('Role change feature coming soon!');
        }

        document.addEventListener('click', function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.classList.remove('show');
                }
            });
        });
    </script>

    <script src="../../../assets/js/main.js"></script>
</body>
</html>
