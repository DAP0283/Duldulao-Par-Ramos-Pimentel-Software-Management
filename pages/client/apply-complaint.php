<?php
/**
 * Complaint Filing Form
 */
session_start();

// Include database and auth functions
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
    // Complainant info
    $full_name = $_POST['full_name'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $address = $_POST['address'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';

    // Complaint-specific fields
    $respondent = $_POST['respondent'] ?? '';
    $incident_date = $_POST['incident_date'] ?? '';
    $incident_location = $_POST['incident_location'] ?? '';
    $category = $_POST['category'] ?? '';
    $details = $_POST['details'] ?? '';

    // Basic validation
    if (empty($full_name) || empty($incident_date) || empty($incident_location) || empty($details)) {
        $error_message = 'Please fill in all required fields';
    } else {
        // Prepare data for storage
        $form_data = array(
            'full_name' => $full_name,
            'birth_date' => $birth_date,
            'address' => $address,
            'contact_number' => $contact_number,
            'respondent' => $respondent,
            'incident_date' => $incident_date,
            'incident_location' => $incident_location,
            'category' => $category,
            'details' => $details
        );

        $result = createApplication($_SESSION['user_id'], 'Complaint', $form_data);

        if ($result['success']) {
            // Redirect to my-applications to avoid form resubmission on back button
            header('Location: my-applications.php?application_id=' . urlencode($result['application_id']) . '&success=1');
            exit();
        } else {
            $error_message = 'Error submitting complaint: ' . $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File a Complaint - Barangay e-Services</title>
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
                    <li><a href="../../auth/2fa-setup.php">Security Settings</a></li>
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-navbar">
                <div class="navbar-content">
                    <h2>File a Complaint</h2>
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

                    <form method="POST" action="" id="complaint-form">
                        <h3>Complainant Information</h3>

                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name" required 
                                   class="form-control" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="birth_date">Date of Birth</label>
                                <input type="date" id="birth_date" name="birth_date" 
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
                            <label for="address">Address</label>
                            <textarea id="address" name="address" class="form-control" rows="2"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                        </div>

                        <h3>Complaint Details</h3>

                        <div class="form-group">
                            <label for="respondent">Respondent (Person/Entity the complaint is against)</label>
                            <input type="text" id="respondent" name="respondent" class="form-control"
                                   value="<?php echo htmlspecialchars($_POST['respondent'] ?? ''); ?>" placeholder="Name of person or entity"> 
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="incident_date">Date of Incident *</label>
                                <input type="date" id="incident_date" name="incident_date" required class="form-control"
                                       value="<?php echo htmlspecialchars($_POST['incident_date'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="incident_location">Incident Location *</label>
                                <input type="text" id="incident_location" name="incident_location" required class="form-control"
                                       value="<?php echo htmlspecialchars($_POST['incident_location'] ?? ''); ?>" placeholder="Street, Barangay, City"> 
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" class="form-control">
                                <option value="">Select category (optional)</option>
                                <option value="noise" <?php echo ($_POST['category'] ?? '') === 'noise' ? 'selected' : ''; ?>>Noise Disturbance</option>
                                <option value="harassment" <?php echo ($_POST['category'] ?? '') === 'harassment' ? 'selected' : ''; ?>>Harassment</option>
                                <option value="property" <?php echo ($_POST['category'] ?? '') === 'property' ? 'selected' : ''; ?>>Property Damage</option>
                                <option value="traffic" <?php echo ($_POST['category'] ?? '') === 'traffic' ? 'selected' : ''; ?>>Traffic/Obstruction</option>
                                <option value="other" <?php echo ($_POST['category'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="details">Incident Description *</label>
                            <textarea id="details" name="details" required class="form-control" rows="6" placeholder="Provide a detailed description of the incident, witnesses, and any evidence if available..."><?php echo htmlspecialchars($_POST['details'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Submit Complaint</button>
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="../../assets/js/form-validation.js"></script>
    <script src="../../assets/js/main.js"></script>
</body>
</html>
