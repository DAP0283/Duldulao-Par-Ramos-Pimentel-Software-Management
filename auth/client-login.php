<?php
/**
 * Client Login Page
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
$show_2fa_form = false;
$temp_user_id = null;

// Check if IP is locked out
$clientIP = getClientIp();
$remainingAttempts = getRemainingLoginAttempts($clientIP);
if (isLoginLockedOut($clientIP)) {
    $remaining = getRemainingLockoutTime($clientIP);
    $error_message = "Account temporarily locked due to too many failed attempts. Try again in $remaining minutes.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isLoginLockedOut($clientIP)) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $totp_code = trim($_POST['totp_code'] ?? '');

    if (empty($email) || empty($password)) {
        $error_message = 'Please fill in all fields';
    } else {
        $result = validateLoginWith2FA($email, $password, $totp_code, 'client');
        
        if ($result['success']) {
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['email'] = $result['email'];
            $_SESSION['name'] = $result['name'];
            $_SESSION['user_type'] = 'client';
            
            header('Location: ../pages/client/dashboard.php');
            exit();
        } elseif (isset($result['requires_2fa']) && $result['requires_2fa']) {
            $show_2fa_form = true;
            $temp_user_id = $result['user_id'];
            $success_message = 'Please enter your 2FA code from Google Authenticator';
        } else {
            $error_message = $result['message'];
        }
    }
}

// Recalculate after processing the POST so remaining attempts reflect the latest state.
$remainingAttempts = getRemainingLoginAttempts($clientIP);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Login - Barangay e-Services</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2><?php echo $show_2fa_form ? 'Two-Factor Authentication' : 'Resident Login'; ?></h2>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php if (!$show_2fa_form && !isLoginLockedOut($clientIP)): ?>
                <?php if ($remainingAttempts > 0 && $remainingAttempts < MAX_LOGIN_ATTEMPTS): ?>
                    <div class="alert alert-info">You have <?php echo $remainingAttempts; ?> login attempt(s) remaining.</div>
                <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Enter your email" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <?php elseif ($show_2fa_form): ?>
            <form method="POST" action="">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <input type="hidden" name="password" value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>">
                
                <div class="form-group">
                    <label for="totp_code">Google Authenticator Code</label>
                    <input type="text" id="totp_code" name="totp_code" required 
                           placeholder="Enter 6-digit code" class="form-control" 
                           pattern="[0-9]{6}" maxlength="6" autocomplete="off">
                    <small class="form-text text-muted">Enter the 6-digit code from your Google Authenticator app</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Verify & Login</button>
                <button type="button" class="btn btn-secondary btn-block" onclick="window.location.reload()">Back to Login</button>
            </form>
            <?php endif; ?>

            <div class="auth-footer">
                <?php if (!$show_2fa_form): ?>
                <p>Don't have an account? <a href="client-register.php">Register here</a></p>
                <p><a href="../index.php">Back to Home</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
