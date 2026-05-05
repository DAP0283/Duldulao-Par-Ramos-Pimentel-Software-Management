<?php
/**
 * Authentication Helper Functions
 */

require_once __DIR__ . '/db_config.php';

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
 * Validate Client Login
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
 * Validate Staff Login
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
