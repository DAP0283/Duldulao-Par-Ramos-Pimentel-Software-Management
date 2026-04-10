<?php
/**
 * Barangay Clearance Application Form
 */
session_start();

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
    $full_name = $_POST['full_name'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $address = $_POST['address'] ?? '';
    $purpose = $_POST['purpose'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';

    if (empty($full_name) || empty($date_of_birth) || empty($address) || empty($purpose)) {
        $error_message = 'Please fill in all required fields';
    } else {
        // Save to database
        $form_data = array(
            'full_name' => $full_name,
            'date_of_birth' => $date_of_birth,
            'address' => $address,
            'contact_number' => $contact_number,
            'purpose' => $purpose
        );
        
        $result = createApplication($_SESSION['user_id'], 'Complaint', $form_data);
        
        if ($result['success']) {
            $success_message = 'Complaint submitted successfully! Your Application ID is: ' . $result['application_id'] . '. Your complaint will be processed within 1-2 business days.';
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
    <title>Apply for Barangay Clearance - Barangay e-Services</title>
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
                    <li><a href="../../auth/logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>

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

                    <form method="POST" action="">
                        <h3>Personal Information</h3>

                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name" required 
                                   class="form-control" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth *</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" required 
                                       class="form-control" value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="contact_number">Contact Number</label>
                                <input type="tel" id="contact_number" name="contact_number" 
                                       class="form-control" value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Address *</label>
                            <input type="text" id="address" name="address" required 
                                   class="form-control" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                        </div>

                        <h3 style="margin-top: 30px;">Clearance Details</h3>

                        <div class="form-group">
                            <label for="purpose">Purpose of Clearance *</label>
                            <select id="purpose" name="purpose" required class="form-control">
                                <option value="">Select Purpose</option>
                                <option value="Employment" <?php echo ($_POST['purpose'] ?? '') === 'Employment' ? 'selected' : ''; ?>>Employment</option>
                                <option value="Travel" <?php echo ($_POST['purpose'] ?? '') === 'Travel' ? 'selected' : ''; ?>>Travel</option>
                                <option value="Business" <?php echo ($_POST['purpose'] ?? '') === 'Business' ? 'selected' : ''; ?>>Business</option>
                                <option value="Education" <?php echo ($_POST['purpose'] ?? '') === 'Education' ? 'selected' : ''; ?>>Education</option>
                                <option value="Loan Application" <?php echo ($_POST['purpose'] ?? '') === 'Loan Application' ? 'selected' : ''; ?>>Loan Application</option>
                                <option value="Other" <?php echo ($_POST['purpose'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
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
</body>
</html>
