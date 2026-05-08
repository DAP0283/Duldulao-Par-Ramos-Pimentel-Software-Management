<?php
/**
 * Burial Assistance Application Form
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicant_name = $_POST['applicant_name'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $deceased_name = $_POST['deceased_name'] ?? '';
    $death_date = $_POST['death_date'] ?? '';
    $relationship = $_POST['relationship'] ?? '';
    $assistance_amount = $_POST['assistance_amount'] ?? '';
    $reason = $_POST['reason'] ?? '';

    if (empty($applicant_name) || empty($deceased_name) || empty($death_date)) {
        $error_message = 'Please fill in all required fields';
    } else {
        // Save to database
        $form_data = array(
            'full_name' => $applicant_name,
            'contact_number' => $contact_number,
            'deceased_name' => $deceased_name,
            'death_date' => $death_date,
            'relationship' => $relationship,
            'assistance_amount' => $assistance_amount,
            'reason' => $reason
        );
        
        $result = createApplication($_SESSION['user_id'], 'Burial Assistance', $form_data);
        
        if ($result['success']) {
            // Redirect to my-applications to avoid form resubmission on back button
            header('Location: my-applications.php?application_id=' . urlencode($result['application_id']) . '&success=1');
            exit();
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
    <title>Apply for Burial Assistance - Barangay e-Services</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>Barangay e-Services</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="my-applications.php">My Applications</a></li>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="../../auth/2fa-setup.php">Security Settings</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>Apply for Burial Assistance</h2>
                    <div class="user-info">
                        <a href="dashboard.php" class="btn btn-sm btn-secondary">Back to Dashboard</a>
                        <a href="../../auth/logout.php" class="btn btn-sm btn-danger">Logout</a>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <div class="form-container">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>


                    <form method="POST" action="">
                        <h3>Applicant Information</h3>

                        <div class="form-group">
                            <label for="applicant_name">Your Full Name *</label>
                            <input type="text" id="applicant_name" name="applicant_name" required 
                                   class="form-control" value="<?php echo htmlspecialchars($_POST['applicant_name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="contact_number">Contact Number *</label>
                            <input type="tel" id="contact_number" name="contact_number" required 
                                   class="form-control" value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>">
                        </div>

                        <h3 style="margin-top: 30px;">Deceased Information</h3>

                        <div class="form-group">
                            <label for="deceased_name">Deceased Full Name *</label>
                            <input type="text" id="deceased_name" name="deceased_name" required 
                                   class="form-control" value="<?php echo htmlspecialchars($_POST['deceased_name'] ?? ''); ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="death_date">Date of Death *</label>
                                <input type="date" id="death_date" name="death_date" required 
                                       class="form-control" value="<?php echo htmlspecialchars($_POST['death_date'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="relationship">Relationship *</label>
                                <input type="text" id="relationship" name="relationship" required 
                                       class="form-control" value="<?php echo htmlspecialchars($_POST['relationship'] ?? ''); ?>">
                            </div>
                        </div>

                        <h3 style="margin-top: 30px;">Assistance Details</h3>

                        <div class="form-group">
                            <label for="assistance_amount">Requested Assistance Amount (PHP)</label>
                            <input type="number" id="assistance_amount" name="assistance_amount" 
                                   class="form-control" step="0.01" value="<?php echo htmlspecialchars($_POST['assistance_amount'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="reason">Reason for Request</label>
                            <textarea id="reason" name="reason" rows="4" class="form-control"><?php echo htmlspecialchars($_POST['reason'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Submit Application</button>
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/form-validation.js"></script>
</body>
</html>
