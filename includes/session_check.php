<?php
/**
 * Session Validation Helper
 */

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../../index.php');
        exit();
    }
}

function requireClientLogin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
        header('Location: ../../auth/client-login.php');
        exit();
    }
}

function requireAdminLogin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
        header('Location: ../../auth/admin-login.php');
        exit();
    }
}

function requireStaffLogin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'staff') {
        header('Location: ../../auth/staff-login.php');
        exit();
    }
}

function checkPermission($requiredRole) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
        http_response_code(403);
        die('Access Denied');
    }
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

function getUserName() {
    return $_SESSION['name'] ?? 'Guest';
}

?>
