<?php
/**
 * Client Profile - View and Edit Profile
 */
session_start();

// Validate client session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../../auth/client-login.php');
    exit();
}

// Include database functions
require_once('../../includes/db_config.php');
require_once('../../includes/auth_functions.php');

// Get user information from session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

$error_message = '';
$success_message = '';

// Get client profile from database
$profile = getClientProfile($user_id);

if (!$profile) {
    $error_message = 'Unable to load profile information';
    $profile = array();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $civil_status = trim($_POST['civil_status'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');

    if (empty($first_name) || empty($last_name) || empty($phone) || empty($address)) {
        $error_message = 'Please fill in all required fields';
    } else {
        // Update profile in database
        $update_data = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'middle_name' => $middle_name,
            'phone_number' => $phone,
            'address' => $address,
            'birth_date' => $birthdate ?: null,
            'gender' => $gender ?: null,
            'civil_status' => $civil_status ?: null,
            'occupation' => $occupation ?: null
        );
        
        $result = updateClientProfile($user_id, $update_data);
        
        if ($result['success']) {
            $success_message = 'Profile updated successfully!';
            // Update session name if it changed
            $_SESSION['name'] = $first_name . ' ' . $last_name;
            $user_name = $_SESSION['name'];
            // Refresh profile data
            $profile = getClientProfile($user_id);
        } else {
            $error_message = $result['message'] ?? 'Failed to update profile. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Barangay e-Services</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .profile-container {
            max-width: 700px;
            margin: 0 auto;
        }

        .profile-section {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .profile-section h3 {
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }

        .form-group input,
        .form-group select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-group input[type="date"] {
            font-size: 14px;
        }

        .read-only {
            background-color: #f9fafb;
            color: #6b7280;
            cursor: not-allowed;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .form-actions button {
            flex: 1;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }

        .profile-info {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .profile-info-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 20px;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .profile-info-row:last-child {
            border-bottom: none;
        }

        .profile-info-label {
            font-weight: 600;
            color: #374151;
        }

        .profile-info-value {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Barangay e-Services</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="my-applications.php">My Applications</a></li>
                    <li><a href="profile.php" class="active">My Profile</a></li>
                    <li><a href="../../auth/2fa-setup.php">Security Settings</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation Bar -->
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Profile - <?php echo htmlspecialchars($user_name); ?></h2>
                    <div class="user-info">
                        <a href="../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>

            <!-- Profile Content -->
            <div class="dashboard-content">
                <div class="profile-container">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Profile Information Section -->
                    <div class="profile-section">
                        <h3>Personal Information</h3>
                        
                        <form method="POST">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name <span class="required">*</span></label>
                                    <input type="text" id="first_name" name="first_name" 
                                           value="<?php echo htmlspecialchars($profile['FirstName'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="middle_name">Middle Name</label>
                                    <input type="text" id="middle_name" name="middle_name" 
                                           value="<?php echo htmlspecialchars($profile['MiddleName'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="last_name">Last Name <span class="required">*</span></label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($profile['LastName'] ?? ''); ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email Address <span class="required">*</span></label>
                                    <input type="email" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($profile['Email'] ?? ''); ?>" 
                                           class="read-only" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number <span class="required">*</span></label>
                                    <input type="tel" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($profile['PhoneNumber'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="address">Address <span class="required">*</span></label>
                                <input type="text" id="address" name="address" 
                                       value="<?php echo htmlspecialchars($profile['Address'] ?? ''); ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="birthdate">Date of Birth</label>
                                    <input type="date" id="birthdate" name="birthdate" 
                                           value="<?php echo isset($profile['BirthDate']) ? $profile['BirthDate']->format('Y-m-d') : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <select id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?php echo ($profile['Gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($profile['Gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($profile['Gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="civil_status">Civil Status</label>
                                    <select id="civil_status" name="civil_status">
                                        <option value="">Select Civil Status</option>
                                        <option value="Single" <?php echo ($profile['CivilStatus'] ?? '') === 'Single' ? 'selected' : ''; ?>>Single</option>
                                        <option value="Married" <?php echo ($profile['CivilStatus'] ?? '') === 'Married' ? 'selected' : ''; ?>>Married</option>
                                        <option value="Divorced" <?php echo ($profile['CivilStatus'] ?? '') === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                                        <option value="Widowed" <?php echo ($profile['CivilStatus'] ?? '') === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="occupation">Occupation</label>
                                    <input type="text" id="occupation" name="occupation" 
                                           value="<?php echo htmlspecialchars($profile['Occupation'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                                <button type="reset" class="btn btn-secondary">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <!-- Account Information Section -->
                    <div class="profile-section">
                        <h3>Account Information</h3>
                        <div class="profile-info">
                            <div class="profile-info-row">
                                <div class="profile-info-label">Account Status:</div>
                                <div class="profile-info-value">
                                    <span class="badge badge-success">Active</span>
                                </div>
                            </div>
                            <div class="profile-info-row">
                                <div class="profile-info-label">Member Since:</div>
                                <div class="profile-info-value">
                                    <?php echo isset($profile['CreatedAt']) ? $profile['CreatedAt']->format('F j, Y') : 'N/A'; ?>
                                </div>
                            </div>
                            <div class="profile-info-row">
                                <div class="profile-info-label">Last Updated:</div>
                                <div class="profile-info-value">
                                    <?php echo isset($profile['UpdatedAt']) ? $profile['UpdatedAt']->format('F j, Y g:i A') : 'N/A'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/main.js"></script>
</body>
</html>
