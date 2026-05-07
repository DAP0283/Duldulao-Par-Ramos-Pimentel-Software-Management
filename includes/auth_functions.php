<?php
/**
 * Authentication Helper Functions
 */

require_once __DIR__ . '/db_config.php';

/**
 * Login Security Functions
 */

// Configuration for login security
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT_MINUTES', 2);
define('ATTEMPTS_FILE', __DIR__ . '/../cache/login_attempts.json');

/**
 * Get normalized client IP address
 */
function getClientIp() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    if ($ip === '::1' || $ip === '0:0:0:0:0:0:0:1') {
        return '127.0.0.1';
    }

    return $ip;
}

/**
 * Check if IP is currently locked out due to too many failed attempts
 */
function isLoginLockedOut($ip) {
    $attempts = loadLoginAttempts();

    if (!isset($attempts[$ip])) {
        return false;
    }

    $attemptData = $attempts[$ip];
    $lockoutTime = strtotime($attemptData['lockout_until'] ?? 'now');

    if (time() < $lockoutTime) {
        return true;
    }

    // Lockout expired, reset attempts
    unset($attempts[$ip]);
    saveLoginAttempts($attempts);
    return false;
}

/**
 * Record a failed login attempt
 */
function recordFailedLoginAttempt($ip) {
    $attempts = loadLoginAttempts();

    if (!isset($attempts[$ip])) {
        $attempts[$ip] = ['count' => 0, 'lockout_until' => null];
    }

    $attempts[$ip]['count']++;

    // Lock out after MAX_LOGIN_ATTEMPTS
    if ($attempts[$ip]['count'] >= MAX_LOGIN_ATTEMPTS) {
        if (empty($attempts[$ip]['lockout_until'])) {
            $attempts[$ip]['lockout_until'] = date('Y-m-d H:i:s', time() + (LOGIN_TIMEOUT_MINUTES * 60));
        }
    }

    saveLoginAttempts($attempts);
}

/**
 * Reset login attempts for successful login
 */
function resetLoginAttempts($ip) {
    $attempts = loadLoginAttempts();
    unset($attempts[$ip]);
    saveLoginAttempts($attempts);
}

/**
 * Get remaining lockout time in minutes
 */
function getRemainingLockoutTime($ip) {
    $attempts = loadLoginAttempts();

    if (!isset($attempts[$ip]) || !isset($attempts[$ip]['lockout_until'])) {
        return 0;
    }

    $lockoutTime = strtotime($attempts[$ip]['lockout_until']);
    $remaining = $lockoutTime - time();

    return max(0, ceil($remaining / 60));
}

/**
 * Get current failed login count for IP
 */
function getLoginAttemptCount($ip) {
    $attempts = loadLoginAttempts();
    return isset($attempts[$ip]['count']) ? intval($attempts[$ip]['count']) : 0;
}

/**
 * Get remaining login attempts for IP
 */
function getRemainingLoginAttempts($ip) {
    $failedCount = getLoginAttemptCount($ip);
    $remaining = MAX_LOGIN_ATTEMPTS - $failedCount;
    return max(0, $remaining);
}

/**
 * Load login attempts from file
 */
function loadLoginAttempts() {
    if (!file_exists(ATTEMPTS_FILE)) {
        return [];
    }

    $data = json_decode(file_get_contents(ATTEMPTS_FILE), true);
    return $data ?: [];
}

/**
 * Save login attempts to file
 */
function saveLoginAttempts($attempts) {
    $dir = dirname(ATTEMPTS_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $json = json_encode($attempts);
    $result = file_put_contents(ATTEMPTS_FILE, $json, LOCK_EX);
    if ($result === false) {
        error_log('Failed to save login attempts to ' . ATTEMPTS_FILE . '.');
    }
}

/**
 * Google OAuth and Authenticator Functions
 */

/**
 * Generate TOTP secret for a user
 */
function generateTOTPSecret() {
    return bin2hex(random_bytes(20)); // 40 character hex string
}

/**
 * Generate TOTP code from secret
 */
function generateTOTPCode($secret, $time = null) {
    $time = $time ?: time();
    $timeWindow = floor($time / 30); // 30 second windows

    $secret = hex2bin($secret);
    $timeWindow = pack('J', $timeWindow); // 64-bit big-endian

    $hash = hash_hmac('sha1', $timeWindow, $secret, true);
    $offset = ord($hash[19]) & 0x0F;

    $code = (
        ((ord($hash[$offset]) & 0x7F) << 24) |
        ((ord($hash[$offset + 1]) & 0xFF) << 16) |
        ((ord($hash[$offset + 2]) & 0xFF) << 8) |
        (ord($hash[$offset + 3]) & 0xFF)
    );

    return str_pad($code % 1000000, 6, '0', STR_PAD_LEFT);
}

/**
 * Verify TOTP code
 */
function verifyTOTPCode($secret, $code, $window = 1) {
    $time = time();

    // Check current and adjacent time windows
    for ($i = -$window; $i <= $window; $i++) {
        $checkTime = $time + ($i * 30);
        if (generateTOTPCode($secret, $checkTime) === $code) {
            return true;
        }
    }

    return false;
}

/**
 * Generate Google Authenticator URI
 */
function generateTOTPURI($secret, $accountName, $issuer = 'Barangay e-Services') {
    $uri = 'otpauth://totp/' . urlencode($issuer) . ':' . urlencode($accountName) . '?secret=' . $secret . '&issuer=' . urlencode($issuer);
    return $uri;
}

/**
 * Store TOTP secret for user
 */
function storeTOTPSecret($userId, $userType, $secret) {
    global $conn;

    try {
        $table = $userType === 'client' ? 'Clients' : 'Staff';
        $idColumn = $userType === 'client' ? 'ClientID' : 'StaffID';

        $tsql = "UPDATE $table SET TOTPSecret = ?, Is2FAEnabled = 1, UpdatedAt = GETDATE() WHERE $idColumn = ?";
        $params = array($secret, $userId);
        $stmt = sqlsrv_query($conn, $tsql, $params);

        return $stmt !== false;
    } catch(Exception $e) {
        return false;
    }
}

/**
 * Get TOTP secret for user
 */
function getTOTPSecret($userId, $userType) {
    global $conn;

    try {
        $table = $userType === 'client' ? 'Clients' : 'Staff';
        $idColumn = $userType === 'client' ? 'ClientID' : 'StaffID';

        $tsql = "SELECT TOTPSecret FROM $table WHERE $idColumn = ?";
        $params = array($userId);
        $stmt = sqlsrv_query($conn, $tsql, $params);

        if ($stmt && sqlsrv_has_rows($stmt)) {
            $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            return $result['TOTPSecret'];
        }

        return null;
    } catch(Exception $e) {
        return null;
    }
}

/**
 * Check if 2FA is enabled for user
 */
function is2FAEnabled($userId, $userType) {
    global $conn;

    try {
        $table = $userType === 'client' ? 'Clients' : 'Staff';
        $idColumn = $userType === 'client' ? 'ClientID' : 'StaffID';

        $tsql = "SELECT Is2FAEnabled FROM $table WHERE $idColumn = ?";
        $params = array($userId);
        $stmt = sqlsrv_query($conn, $tsql, $params);

        if ($stmt && sqlsrv_has_rows($stmt)) {
            $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            return $result['Is2FAEnabled'] == 1;
        }

        return false;
    } catch(Exception $e) {
        return false;
    }
}

/**
 * Validate login with 2FA
 */
function validateLoginWith2FA($email, $password, $totpCode = null, $userType = 'client') {
    // First check if account is locked out
    $clientIP = getClientIp();

    if (isLoginLockedOut($clientIP)) {
        $remaining = getRemainingLockoutTime($clientIP);
        return ['success' => false, 'message' => "Account temporarily locked due to too many failed attempts. Try again in $remaining minutes."];
    }

    // Validate credentials
    $validateFunc = $userType === 'client' ? 'validateClientLogin' : 'validateStaffLogin';
    $result = $validateFunc($email, $password);

    if (!$result['success']) {
        $originalMessage = $result['message'] ?? 'Invalid email or password';
        recordFailedLoginAttempt($clientIP);

        if (isLoginLockedOut($clientIP)) {
            $remaining = getRemainingLockoutTime($clientIP);
            return ['success' => false, 'message' => "Account temporarily locked due to too many failed attempts. Try again in $remaining minutes."];
        }

        $remaining = getRemainingLoginAttempts($clientIP);
        return ['success' => false, 'message' => trim($originalMessage) . " ($remaining attempt(s) remaining)."];
    }

    // Check if 2FA is enabled
    if (is2FAEnabled($result['user_id'], $userType)) {
        if (empty($totpCode)) {
            return ['success' => false, 'message' => '2FA code required', 'requires_2fa' => true, 'user_id' => $result['user_id']];
        }

        $secret = getTOTPSecret($result['user_id'], $userType);
        if (!$secret || !verifyTOTPCode($secret, $totpCode)) {
            recordFailedLoginAttempt($clientIP);

            if (isLoginLockedOut($clientIP)) {
                $remaining = getRemainingLockoutTime($clientIP);
                return ['success' => false, 'message' => "Account temporarily locked due to too many failed attempts. Try again in $remaining minutes."];
            }

            $remaining = getRemainingLoginAttempts($clientIP);
            return ['success' => false, 'message' => "Invalid 2FA code. $remaining attempt(s) remaining."];
        }
    }

    // Success - reset attempts
    resetLoginAttempts($clientIP);
    return $result;
}

/**
 * Hash a password using bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify a password against its hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Validate Client Login (Legacy - use validateLoginWith2FA for new implementations)
 */
function validateClientLogin($email, $password) {
    global $conn;
    
    try {
        $tsql = "EXEC sp_ValidateClientLogin @Email = ?, @Password = ?";
        $params = array($email, $password);
        $stmt = sqlsrv_query($conn, $tsql, $params);
        
        if ($stmt === false) {
            return ['success' => false, 'message' => 'Database error: ' . print_r(sqlsrv_errors(), true)];
        }
        
        $client = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        
        if ($client) {
            return [
                'success' => true,
                'user_id' => $client['ClientID'],
                'email' => $client['Email'],
                'name' => $client['FirstName'] . ' ' . $client['LastName'],
                'user_type' => 'client'
            ];
        } else {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
    } catch(Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Validate Admin Login
 */
function validateAdminLogin($username, $password) {
    global $conn;
    
    try {
        $tsql = "EXEC sp_ValidateAdminLogin @Username = ?, @Password = ?";
        $params = array($username, $password);
        $stmt = sqlsrv_query($conn, $tsql, $params);
        
        if ($stmt === false) {
            return ['success' => false, 'message' => 'Database error: ' . print_r(sqlsrv_errors(), true)];
        }
        
        $admin = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        
        if ($admin) {
            return [
                'success' => true,
                'user_id' => $admin['AdminID'],
                'username' => $admin['Username'],
                'name' => $admin['FullName'],
                'user_type' => 'admin'
            ];
        } else {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
    } catch(Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Validate Staff Login (Legacy - use validateLoginWith2FA for new implementations)
 */
function validateStaffLogin($email, $password) {
    global $conn;
    
    try {
        $tsql = "EXEC sp_ValidateStaffLogin @Email = ?, @Password = ?";
        $params = array($email, $password);
        $stmt = sqlsrv_query($conn, $tsql, $params);
        
        if ($stmt === false) {
            return ['success' => false, 'message' => 'Database error: ' . print_r(sqlsrv_errors(), true)];
        }
        
        $staff = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        
        if ($staff) {
            return [
                'success' => true,
                'user_id' => $staff['StaffID'],
                'email' => $staff['Email'],
                'name' => $staff['FirstName'] . ' ' . $staff['LastName'],
                'role' => $staff['Role'],
                'position' => $staff['Position'],
                'user_type' => 'staff'
            ];
        } else {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
    } catch(Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Register New Client
 */
function registerClient($email, $password, $firstName, $lastName, $middleName = null, $phoneNumber = null, $address = null, $birthDate = null, $gender = null, $civilStatus = null, $occupation = null) {
    global $conn;
    
    try {
        // Check if email already exists
        $checkTsql = "EXEC sp_CheckEmailExists @Email = ?, @UserType = 'Client'";
        $checkParams = array($email);
        $checkStmt = sqlsrv_query($conn, $checkTsql, $checkParams);
        
        if ($checkStmt === false) {
            return ['success' => false, 'message' => 'Database error: ' . print_r(sqlsrv_errors(), true)];
        }
        
        $result = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
        
        if ($result !== false && $result['Count'] > 0) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Insert client directly into database with all details
        $tsql = "INSERT INTO Clients (Email, Password, FirstName, LastName, MiddleName, PhoneNumber, Address, BirthDate, Gender, CivilStatus, Occupation, IsActive, CreatedAt, UpdatedAt)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, GETDATE(), GETDATE())";
        
        $params = array(
            $email,
            $password,
            $firstName,
            $lastName,
            $middleName,
            $phoneNumber,
            $address,
            $birthDate ? $birthDate : null,
            $gender,
            $civilStatus,
            $occupation
        );
        
        $stmt = sqlsrv_query($conn, $tsql, $params);
        
        if ($stmt === false) {
            return ['success' => false, 'message' => 'Database error: ' . print_r(sqlsrv_errors(), true)];
        }
        
        // Get the inserted client ID by querying for the email we just inserted
        $getIdTsql = "SELECT ClientID FROM Clients WHERE Email = ?";
        $getIdParams = array($email);
        $getIdStmt = sqlsrv_query($conn, $getIdTsql, $getIdParams);
        
        if ($getIdStmt === false) {
            return ['success' => false, 'message' => 'Database error retrieving ID: ' . print_r(sqlsrv_errors(), true)];
        }
        
        $idResult = sqlsrv_fetch_array($getIdStmt, SQLSRV_FETCH_ASSOC);
        
        if ($idResult !== false && isset($idResult['ClientID'])) {
            return [
                'success' => true,
                'message' => 'Registration successful',
                'client_id' => $idResult['ClientID']
            ];
        } else {
            return ['success' => false, 'message' => 'Registration failed - could not retrieve client ID'];
        }
    } catch(Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Get Client Profile
 */
function getClientProfile($clientId) {
    global $conn;
    
    try {
        $tsql = "EXEC sp_GetClientProfile @ClientID = ?";
        $params = array($clientId);
        $stmt = sqlsrv_query($conn, $tsql, $params);
        
        if ($stmt === false) {
            return null;
        }
        
        return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    } catch(Exception $e) {
        return null;
    }
}

/**
 * Update Client Profile
 */
function updateClientProfile($clientId, $data) {
    global $conn;
    
    try {
        $tsql = "EXEC sp_UpdateClientProfile @ClientID = ?, @PhoneNumber = ?, @Address = ?, @BirthDate = ?, @Gender = ?, @CivilStatus = ?, @Occupation = ?";
        $params = array(
            $clientId,
            $data['phone_number'] ?? null,
            $data['address'] ?? null,
            $data['birth_date'] ?? null,
            $data['gender'] ?? null,
            $data['civil_status'] ?? null,
            $data['occupation'] ?? null
        );
        $stmt = sqlsrv_query($conn, $tsql, $params);
        
        if ($stmt === false) {
            return ['success' => false, 'message' => 'Database error: ' . print_r(sqlsrv_errors(), true)];
        }
        
        return ['success' => true, 'message' => 'Profile updated successfully'];
    } catch(Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Create Application
 */
function createApplication($clientId, $serviceType, $applicationData = null) {
    global $conn;
    
    try {
        $tsql = "EXEC sp_CreateApplication @ClientID = ?, @ServiceType = ?, @ApplicationData = ?";
        $params = array($clientId, $serviceType, $applicationData ? json_encode($applicationData) : null);
        $stmt = sqlsrv_query($conn, $tsql, $params);
        
        if ($stmt === false) {
            $errors = sqlsrv_errors();
            return ['success' => false, 'message' => 'Database error: ' . $errors[0]['message']];
        }
        
        $application_id = null;
        
        // Check if there are rows in the result set
        if (sqlsrv_has_rows($stmt)) {
            $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            
            if ($result === false) {
                $errors = sqlsrv_errors();
                return ['success' => false, 'message' => 'Database error: ' . $errors[0]['message']];
            }
            
            if ($result['Result'] === 'Success') {
                $application_id = $result['ApplicationID'] ?? null;
                
                // If still null, fetch the latest application for this client
                if ($application_id === null) {
                    $latest_stmt = sqlsrv_query($conn, 
                        "SELECT TOP 1 ApplicationID FROM Applications WHERE ClientID = ? ORDER BY CreatedAt DESC", 
                        array($clientId)
                    );
                    if ($latest_stmt && sqlsrv_has_rows($latest_stmt)) {
                        $latest = sqlsrv_fetch_array($latest_stmt, SQLSRV_FETCH_ASSOC);
                        $application_id = $latest['ApplicationID'] ?? null;
                    }
                }
                
                return [
                    'success' => true,
                    'message' => 'Application submitted successfully',
                    'application_id' => $application_id
                ];
            } else {
                return ['success' => false, 'message' => $result['Message'] ?? 'Application submission failed'];
            }
        } else {
            // No rows returned from stored procedure, fetch the latest application for this client
            $latest_stmt = sqlsrv_query($conn, 
                "SELECT TOP 1 ApplicationID FROM Applications WHERE ClientID = ? ORDER BY CreatedAt DESC", 
                array($clientId)
            );
            if ($latest_stmt && sqlsrv_has_rows($latest_stmt)) {
                $latest = sqlsrv_fetch_array($latest_stmt, SQLSRV_FETCH_ASSOC);
                $application_id = $latest['ApplicationID'] ?? null;
            }
            
            return [
                'success' => true, 
                'message' => 'Application submitted successfully', 
                'application_id' => $application_id
            ];
        }
    } catch(Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Get Client Applications
 */
function getClientApplications($clientId) {
    global $conn;
    
    try {
        $tsql = "EXEC sp_GetClientApplications @ClientID = ?";
        $params = array($clientId);
        $stmt = sqlsrv_query($conn, $tsql, $params);
        
        if ($stmt === false) {
            return [];
        }
        
        $results = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Get status badge class
            $status = $row['Status'] ?? 'Pending';
            $status_class = 'badge-secondary';
            if ($status === 'Pending') $status_class = 'badge-warning';
            elseif ($status === 'Processing' || $status === 'In Progress') $status_class = 'badge-info';
            elseif ($status === 'Approved' || $status === 'Completed') $status_class = 'badge-success';
            elseif ($status === 'Rejected' || $status === 'Cancelled') $status_class = 'badge-danger';
            
            // Format date
            $created_at = $row['CreatedAt'];
            if ($created_at instanceof DateTime) {
                $date_formatted = $created_at->format('Y-m-d');
            } else {
                $date_formatted = date('Y-m-d', strtotime($created_at));
            }
            
            // Format application ID with prefix
            $app_id = '#APP-' . str_pad($row['ApplicationID'], 5, '0', STR_PAD_LEFT);
            
            $results[] = array(
                'id' => $app_id,
                'application_id' => $row['ApplicationID'],
                'service' => $row['ServiceType'],
                'date' => $date_formatted,
                'status' => $status,
                'status_class' => $status_class,
                'notes' => $row['ProcessingNotes'] ?? ''
            );
        }
        return $results;
    } catch(Exception $e) {
        return [];
    }
}

/**
 * Get Application Details
 */
function getApplicationDetails($applicationId) {
    global $conn;
    
    try {
        $tsql = "EXEC sp_GetApplicationDetails @ApplicationID = ?";
        $params = array($applicationId);
        $stmt = sqlsrv_query($conn, $tsql, $params);
        
        if ($stmt === false) {
            return null;
        }
        
        return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    } catch(Exception $e) {
        return null;
    }
}

/**
 * Update Application Status (For Staff)
 */
function updateApplicationStatus($applicationId, $status, $processingNotes = null, $staffId = null) {
    global $conn;
    
    try {
        $tsql = "EXEC sp_UpdateApplicationStatus @ApplicationID = ?, @Status = ?, @ProcessingNotes = ?, @ApprovedByStaffID = ?";
        $params = array($applicationId, $status, $processingNotes, $staffId);
        $stmt = sqlsrv_query($conn, $tsql, $params);
        
        if ($stmt === false) {
            return ['success' => false, 'message' => 'Database error: ' . print_r(sqlsrv_errors(), true)];
        }
        
        return ['success' => true, 'message' => 'Application status updated'];
    } catch(Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Get All Applications (For Admin)
 */
function getAllApplications($status = null) {
    global $conn;
    
    try {
        $tsql = "EXEC sp_GetAllApplications @Status = ?";
        $params = array($status);
        $stmt = sqlsrv_query($conn, $tsql, $params);
        
        if ($stmt === false) {
            return [];
        }
        
        $results = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }
        return $results;
    } catch(Exception $e) {
        return [];
    }
}

/**
 * Get Staff Members
 */
function getStaffMembers($role = null) {
    global $conn;
    
    try {
        $tsql = "EXEC sp_GetStaffMembers @Role = ?";
        $params = array($role);
        $stmt = sqlsrv_query($conn, $tsql, $params);
        
        if ($stmt === false) {
            return [];
        }
        
        $results = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }
        return $results;
    } catch(Exception $e) {
        return [];
    }
}

/**
 * Get Pending Applications for Staff
 */
function getPendingApplicationsForStaff($staffId) {
    global $conn;
    
    try {
        $tsql = "EXEC sp_GetPendingApplicationsForStaff @StaffID = ?";
        $params = array($staffId);
        $stmt = sqlsrv_query($conn, $tsql, $params);
        
        if ($stmt === false) {
            return [];
        }
        
        $results = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }
        return $results;
    } catch(Exception $e) {
        return [];
    }
}

?>
