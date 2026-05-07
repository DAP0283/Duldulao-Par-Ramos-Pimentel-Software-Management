<?php
/**
 * 2FA Setup Page
 */
session_start();
require_once __DIR__ . '/../includes/auth_functions.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$user_name = $_SESSION['name'];
$user_email = $_SESSION['email'];

$message = '';
$secret = '';
$qr_code_url = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['setup_2fa'])) {
        // Generate new TOTP secret
        $secret = generateTOTPSecret();
        $qr_code_url = generateTOTPURI($secret, $user_email);
        $_SESSION['temp_totp_secret'] = $secret;
        $message = 'Scan the QR code with Google Authenticator, then enter the code to verify.';
    } elseif (isset($_POST['verify_2fa'])) {
        $entered_code = trim($_POST['verification_code'] ?? '');
        $secret = $_SESSION['temp_totp_secret'] ?? '';

        if (empty($entered_code)) {
            $message = 'Please enter the verification code.';
        } elseif (verifyTOTPCode($secret, $entered_code)) {
            // Store the secret in database
            if (storeTOTPSecret($user_id, $user_type, $secret)) {
                unset($_SESSION['temp_totp_secret']);
                $message = 'Two-factor authentication has been successfully enabled!';
                header('refresh:2;url=' . ($user_type === 'client' ? '../pages/client/dashboard.php' : '../pages/staff/dashboard.php'));
            } else {
                $message = 'Failed to save 2FA settings. Please try again.';
            }
        } else {
            $message = 'Invalid verification code. Please try again.';
        }
    } elseif (isset($_POST['disable_2fa'])) {
        // Disable 2FA
        global $conn;
        try {
            $table = $user_type === 'client' ? 'Clients' : 'Staff';
            $idColumn = $user_type === 'client' ? 'ClientID' : 'StaffID';

            $tsql = "UPDATE $table SET TOTPSecret = NULL, Is2FAEnabled = 0, UpdatedAt = GETDATE() WHERE $idColumn = ?";
            $params = array($user_id);
            $stmt = sqlsrv_query($conn, $tsql, $params);

            if ($stmt) {
                $message = 'Two-factor authentication has been disabled.';
            } else {
                $message = 'Failed to disable 2FA. Please try again.';
            }
        } catch(Exception $e) {
            $message = 'Error: ' . $e->getMessage();
        }
    }
}

// Check if 2FA is already enabled
$is_2fa_enabled = is2FAEnabled($user_id, $user_type);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication Setup - Barangay e-Services</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box auth-box-large">
            <h2>Two-Factor Authentication Setup</h2>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo strpos($message, 'success') !== false ? 'success' : (strpos($message, 'Invalid') !== false ? 'danger' : 'info'); ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($is_2fa_enabled): ?>
                <div class="alert alert-success">
                    <strong>✓ Two-factor authentication is currently enabled</strong><br>
                    Your account is protected with Google Authenticator.
                </div>

                <form method="POST" action="">
                    <button type="submit" name="disable_2fa" class="btn btn-danger btn-block"
                            onclick="return confirm('Are you sure you want to disable two-factor authentication? This will make your account less secure.')">
                        Disable 2FA
                    </button>
                </form>
            <?php elseif (!isset($_SESSION['temp_totp_secret'])): ?>
                <div class="setup-instructions">
                    <h3>How to set up Google Authenticator:</h3>
                    <ol>
                        <li>Download Google Authenticator app from your app store (iOS/Android)</li>
                        <li>Click "Set up 2FA" below to generate a QR code</li>
                        <li>Scan the QR code with the Google Authenticator app</li>
                        <li>Enter the 6-digit code to verify and enable 2FA</li>
                    </ol>
                </div>

                <form method="POST" action="">
                    <button type="submit" name="setup_2fa" class="btn btn-primary btn-block">
                        Set up 2FA
                    </button>
                </form>
            <?php else: ?>
                <div class="qr-code-section">
                    <p>Scan this QR code with your Google Authenticator app:</p>
                    <div style="text-align: center; margin: 20px 0;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($qr_code_url); ?>" alt="QR Code" style="border: 1px solid #ddd; padding: 10px;">
                    </div>
                    <p style="font-size: 12px; color: #666; word-break: break-all;">
                        Or manually enter this code: <strong><?php echo $secret; ?></strong>
                    </p>
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="verification_code">Enter verification code from Google Authenticator:</label>
                        <input type="text" id="verification_code" name="verification_code" required
                               placeholder="000000" class="form-control"
                               pattern="[0-9]{6}" maxlength="6" autocomplete="off">
                    </div>

                    <button type="submit" name="verify_2fa" class="btn btn-success btn-block">
                        Verify & Enable 2FA
                    </button>
                    <button type="button" class="btn btn-secondary btn-block" onclick="window.location.reload()">
                        Cancel
                    </button>
                </form>
            <?php endif; ?>

            <div class="auth-footer">
                <p>
                    <a href="<?php echo $user_type === 'client' ? '../pages/client/dashboard.php' : '../pages/staff/dashboard.php'; ?>">
                        ← Back to Dashboard
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>