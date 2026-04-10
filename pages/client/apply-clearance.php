<?php
/**
 * Barangay Clearance Application Form
 */
session_start();

// Include database functions
require_once('../../includes/db_config.php');
require_once('../../includes/auth_functions.php');

// Validate client session
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header('Location: ../../auth/client-login.php');
    exit();
}

// Get user information from session
$user_name = $_SESSION['name'];

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $full_name = $_POST['full_name'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $address = $_POST['address'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $purpose = $_POST['purpose'] ?? '';
    $reason = $_POST['reason'] ?? '';

    // Validate
    if (empty($full_name) || empty($birth_date) || empty($address) || empty($purpose)) {
        $error_message = 'Please fill in all required fields';
    } else {
        // Save to database
        $form_data = array(
            'full_name' => $full_name,
            'birth_date' => $birth_date,
            'address' => $address,
            'contact_number' => $contact_number,
            'purpose' => $purpose,
            'reason' => $reason
        );
        
        $result = createApplication($_SESSION['user_id'], 'Barangay Clearance', $form_data);
        
        if ($result['success']) {
            $success_message = 'Clearance application submitted successfully! Your Application ID is: ' . $result['application_id'] . '. You will receive updates via email.';
        } else {
            $error_message = 'Error submitting application: ' . $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Barangay Clearance - Barangay e-Services</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
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
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Apply for Barangay Clearance</h2>
                    <a href="dashboard.php" class="btn btn-sm btn-secondary">Back to Dashboard</a>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="form-container">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                        <p><a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a></p>
                    <?php else: ?>

                    <form method="POST" action="" id="clearance-form">
                        <h3>Personal Information</h3>

                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name" required 
                                   class="form-control" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="birth_date">Date of Birth *</label>
                                <input type="date" id="birth_date" name="birth_date" required 
                                       class="form-control" value="<?php echo htmlspecialchars($_POST['birth_date'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="contact_number">Contact Number</label>
                                <input type="tel" id="contact_number" name="contact_number" 
                                       class="form-control" placeholder="09XX-XXX-XXXX"
                                       value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Address *</label>
                            <textarea id="address" name="address" required class="form-control" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                        </div>

                        <h3>Clearance Details</h3>

                        <div class="form-group">
                            <label for="purpose">Purpose of Clearance *</label>
                            <select id="purpose" name="purpose" required class="form-control">
                                <option value="">Select a purpose</option>
                                <option value="employment" <?php echo ($_POST['purpose'] ?? '') === 'employment' ? 'selected' : ''; ?>>Employment</option>
                                <option value="business" <?php echo ($_POST['purpose'] ?? '') === 'business' ? 'selected' : ''; ?>>Business Registration</option>
                                <option value="immigration" <?php echo ($_POST['purpose'] ?? '') === 'immigration' ? 'selected' : ''; ?>>Immigration/Travel</option>
                                <option value="education" <?php echo ($_POST['purpose'] ?? '') === 'education' ? 'selected' : ''; ?>>Educational Purpose</option>
                                <option value="housing" <?php echo ($_POST['purpose'] ?? '') === 'housing' ? 'selected' : ''; ?>>Housing Loan</option>
                                <option value="other" <?php echo ($_POST['purpose'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="reason">Additional Reason/Details</label>
                            <textarea id="reason" name="reason" class="form-control" rows="4" 
                                      placeholder="Please provide any additional information..."><?php echo htmlspecialchars($_POST['reason'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Submit Application</button>
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>

                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/form-validation.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html>
