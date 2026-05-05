<?php
/**
 * Logout Handler
 */
session_start();

// Determine user type before destroying session
$user_type = $_SESSION['user_type'] ?? null;

// Destroy the session
session_destroy();

// Redirect based on user type
if ($user_type === 'client') {
    header('Location: client-login.php');
} elseif ($user_type === 'staff') {
    header('Location: staff-login.php');
} elseif ($user_type === 'admin') {
    header('Location: admin-login.php');
} else {
    header('Location: ../index.php');
}
exit();
