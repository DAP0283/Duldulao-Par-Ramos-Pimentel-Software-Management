<?php
/**
 * Client Registration Page
 */
session_start();
require_once __DIR__ . '/../includes/auth_functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'client') {
    header('Location: ../pages/client/dashboard.php');
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $civil_status = trim($_POST['civil_status'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password) || empty($address) || empty($birthdate) || empty($gender) || empty($civil_status)) {
        $error_message = 'Please fill in all required fields';
    } elseif ($password !== $password_confirm) {
        $error_message = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address';
    } else {
        // Register client with all details
        $result = registerClient($email, $password, $first_name, $last_name, $middle_name, $phone, $address, $birthdate, $gender, $civil_status, $occupation);
        
        if ($result['success']) {
            $success_message = 'Registration successful! Redirecting to login...';
            header('refresh:2;url=client-login.php');
        } else {
            $error_message = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Registration - Barangay e-Services</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box auth-box-large">
            <h2>Client Registration</h2>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <p><a href="client-login.php" class="btn btn-primary btn-block">Go to Login</a></p>
            <?php else: ?>

            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" required 
                               placeholder="First Name" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" required 
                               placeholder="Last Name" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name"
                           placeholder="Middle Name (Optional)" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['middle_name'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" required 
                               placeholder="Enter your email" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" required 
                               placeholder="09XXXXXXXXX" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Address <span class="required">*</span></label>
                    <input type="text" id="address" name="address" required 
                           placeholder="Street, Barangay, Municipality" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="birthdate">Birth Date <span class="required">*</span></label>
                        <input type="date" id="birthdate" name="birthdate" required 
                               class="form-control"
                               value="<?php echo htmlspecialchars($_POST['birthdate'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender <span class="required">*</span></label>
                        <select id="gender" name="gender" required class="form-control">
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($_POST['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($_POST['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($_POST['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="civil_status">Civil Status <span class="required">*</span></label>
                        <select id="civil_status" name="civil_status" required class="form-control">
                            <option value="">Select Civil Status</option>
                            <option value="Single" <?php echo ($_POST['civil_status'] ?? '') === 'Single' ? 'selected' : ''; ?>>Single</option>
                            <option value="Married" <?php echo ($_POST['civil_status'] ?? '') === 'Married' ? 'selected' : ''; ?>>Married</option>
                            <option value="Divorced" <?php echo ($_POST['civil_status'] ?? '') === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                            <option value="Widowed" <?php echo ($_POST['civil_status'] ?? '') === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="occupation">Occupation</label>
                        <input type="text" id="occupation" name="occupation"
                               placeholder="Job Title (Optional)" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['occupation'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Enter your password (min 6 characters)" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">Confirm Password <span class="required">*</span></label>
                        <input type="password" id="password_confirm" name="password_confirm" required 
                               placeholder="Confirm your password" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="client-login.php">Login here</a></p>
                <p><a href="../index.php">Back to Home</a></p>
            </div>

            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
