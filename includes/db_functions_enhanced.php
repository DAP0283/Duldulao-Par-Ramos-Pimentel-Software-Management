<?php
/**
 * Enhanced Database Helper Functions
 * Additional utility functions for dashboard, statistics, and features
 * 
 * NOTE: createApplication() and getClientApplications() are already in auth_functions.php
 * This file provides helper functions only
 */

require_once('db_config.php');

// =====================================================
// HELPER FUNCTIONS - Status formatting
// =====================================================

/**
 * Get status badge CSS class for UI display
 */
// function getStatusBadgeClass($status) {
//     $badge_map = array(
//         'Pending' => 'badge-warning',
//         'Processing' => 'badge-info',
//         'In Progress' => 'badge-info',
//         'Approved' => 'badge-success',
//        'Completed' => 'badge-success',
 //       'Rejected' => 'badge-danger',
 //       'Cancelled' => 'badge-danger'
   // );
    
    //return $badge_map[$status] ?? 'badge-secondary';
//}

// =====================================================
// PLACEHOLDER - Get client applications
// NOTE: This function is already in auth_functions.php
// DO NOT UNCOMMENT - it will cause duplicate function error
// =====================================================

/*
function getClientApplications($client_id) {
    global $conn;
    
    try {
        $query = "EXEC sp_GetClientApplications @ClientID = ?";
        $result = sqlsrv_query($conn, $query, array($client_id));
        
        if ($result === false) {
            return array();
        }
        
        $applications = array();
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $applications[] = array(
                'id' => '#APP-' . str_pad($row['ApplicationID'], 3, '0', STR_PAD_LEFT),
                'service' => $row['ServiceType'],
                'date' => ($row['CreatedAt'] instanceof DateTime) ? 
                    $row['CreatedAt']->format('Y-m-d') : 
                    date('Y-m-d', strtotime($row['CreatedAt'])),
                'status' => $row['Status'],
                'status_class' => getStatusBadgeClass($row['Status']),
                'notes' => $row['ProcessingNotes'],
                'application_id' => $row['ApplicationID']
            );
        }
        
        return $applications;
        
    } catch (Exception $e) {
        return array();
    }
}

/**
 * Get single application details
 */
function getApplicationDetails($application_id) {
    global $conn;
    
    try {
        $query = "EXEC sp_GetApplicationDetails @ApplicationID = ?";
        $result = sqlsrv_query($conn, $query, array($application_id));
        
        if ($result === false) {
            return null;
        }
        
        $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
        
        if ($row) {
            // Parse JSON data
            $app_data = json_decode($row['ApplicationData'], true);
            
            return array(
                'id' => $row['ApplicationID'],
                'service_type' => $row['ServiceType'],
                'status' => $row['Status'],
                'created_at' => ($row['CreatedAt'] instanceof DateTime) ? 
                    $row['CreatedAt']->format('Y-m-d H:i:s') : 
                    $row['CreatedAt'],
                'processing_notes' => $row['ProcessingNotes'],
                'approved_by' => $row['ApprovedBy'],
                'approval_date' => $row['ApprovalDate'],
                'data' => $app_data
            );
        }
        
        return null;
        
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Update application status
 */
function updateApplicationStatus($application_id, $status, $notes = '', $approved_by = null) {
    global $conn;
    
    try {
        $query = "EXEC sp_UpdateApplicationStatus 
            @ApplicationID = ?,
            @Status = ?,
            @ProcessingNotes = ?,
            @ApprovedByStaffID = ?";
        
        $params = array($application_id, $status, $notes, $approved_by);
        $result = sqlsrv_query($conn, $query, $params);
        
        if ($result === false) {
            return false;
        }
        
        return true;
        
    } catch (Exception $e) {
        return false;
    }
}

// =====================================================
// STAFF FUNCTIONS (Fix role change placeholder)
// =====================================================

/**
 * Update staff role with audit logging
 */
function updateStaffRole($staff_id, $new_role, $updated_by, $notes = '') {
    global $conn;
    
    try {
        $query = "EXEC sp_UpdateStaffRole 
            @StaffID = ?,
            @NewRole = ?,
            @UpdatedByStaffID = ?,
            @Notes = ?";
        
        $params = array($staff_id, $new_role, $updated_by, $notes);
        $result = sqlsrv_query($conn, $query, $params);
        
        if ($result === false) {
            $errors = sqlsrv_errors();
            return array('success' => false, 'error' => $errors[0]['message']);
        }
        
        $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
        
        return array(
            'success' => ($row['Result'] === 'Success'),
            'message' => $row['Message']
        );
        
    } catch (Exception $e) {
        return array('success' => false, 'error' => $e->getMessage());
    }
}

/**
 * Get staff members by role
 */
function getStaffByRole($role) {
    global $conn;
    
    try {
        $query = "EXEC sp_GetStaffByRole @Role = ?";
        $result = sqlsrv_query($conn, $query, array($role));
        
        if ($result === false) {
            return array();
        }
        
        $staff = array();
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $staff[] = $row;
        }
        
        return $staff;
        
    } catch (Exception $e) {
        return array();
    }
}

/**
 * Get all staff members with optional role filter
 */
function getAllStaff($role = null) {
    global $conn;
    
    try {
        $query = "EXEC sp_GetStaffMembers @Role = ?";
        $result = sqlsrv_query($conn, $query, array($role));
        
        if ($result === false) {
            return array();
        }
        
        $staff = array();
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $staff[] = $row;
        }
        
        return $staff;
        
    } catch (Exception $e) {
        return array();
    }
}

// =====================================================
// DASHBOARD/STATISTICS FUNCTIONS
// =====================================================

/**
 * Get staff dashboard statistics
 */
function getStaffDashboardStats($staff_id) {
    global $conn;
    
    try {
        $query = "EXEC sp_GetStaffDashboardStats @StaffID = ?";
        $result = sqlsrv_query($conn, $query, array($staff_id));
        
        if ($result === false) {
            return null;
        }
        
        return sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
        
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get application statistics
 */
function getApplicationStats() {
    global $conn;
    
    try {
        $query = "SELECT * FROM vw_ApplicationStats";
        $result = sqlsrv_query($conn, $query);
        
        if ($result === false) {
            return array(
                'TotalApplications' => 0,
                'PendingCount' => 0,
                'ProcessingCount' => 0,
                'ApprovedCount' => 0,
                'RejectedCount' => 0
            );
        }
        
        return sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
        
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get pending applications for staff
 */
function getPendingApplicationsForStaff($staff_id) {
    global $conn;
    
    try {
        $query = "EXEC sp_GetPendingApplicationsForStaff @StaffID = ?";
        $result = sqlsrv_query($conn, $query, array($staff_id));
        
        if ($result === false) {
            return array();
        }
        
        $applications = array();
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $applications[] = $row;
        }
        
        return $applications;
        
    } catch (Exception $e) {
        return array();
    }
}

/**
 * Get all applications (admin/punong barangay)
 */
function getAllApplications($status = null) {
    global $conn;
    
    try {
        $query = "EXEC sp_GetAllApplications @Status = ?";
        $result = sqlsrv_query($conn, $query, array($status));
        
        if ($result === false) {
            return array();
        }
        
        $applications = array();
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $applications[] = $row;
        }
        
        return $applications;
        
    } catch (Exception $e) {
        return array();
    }
}

// =====================================================
// MESSAGE FUNCTIONS (Fix messages.php placeholder)
// =====================================================

/**
 * Send message between staff
 */
function sendMessage($sender_id, $recipient_id, $subject, $message, $message_type = 'Personal', $app_id = null) {
    global $conn;
    
    try {
        $query = "EXEC sp_SendMessage 
            @SenderID = ?,
            @RecipientID = ?,
            @Subject = ?,
            @MessageBody = ?,
            @MessageType = ?,
            @RelatedApplicationID = ?";
        
        $params = array($sender_id, $recipient_id, $subject, $message, $message_type, $app_id);
        $result = sqlsrv_query($conn, $query, $params);
        
        if ($result === false) {
            return false;
        }
        
        return true;
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get unread messages for staff member
 */
function getUnreadMessages($staff_id) {
    global $conn;
    
    try {
        $query = "EXEC sp_GetUnreadMessages @StaffID = ?";
        $result = sqlsrv_query($conn, $query, array($staff_id));
        
        if ($result === false) {
            return array();
        }
        
        $messages = array();
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $messages[] = $row;
        }
        
        return $messages;
        
    } catch (Exception $e) {
        return array();
    }
}

/**
 * Mark message as read
 */
function markMessageAsRead($message_id) {
    global $conn;
    
    try {
        $query = "EXEC sp_MarkMessageAsRead @MessageID = ?";
        $result = sqlsrv_query($conn, $query, array($message_id));
        
        return ($result !== false);
        
    } catch (Exception $e) {
        return false;
    }
}

// =====================================================
// BUDGET FUNCTIONS (Finance management)
// =====================================================

/**
 * Get budget summary for fiscal year
 */
function getBudgetSummary($fiscal_year) {
    global $conn;
    
    try {
        $query = "EXEC sp_GetBudgetSummary @FiscalYear = ?";
        $result = sqlsrv_query($conn, $query, array($fiscal_year));
        
        if ($result === false) {
            return array();
        }
        
        $budget = array();
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $budget[] = $row;
        }
        
        return $budget;
        
    } catch (Exception $e) {
        return array();
    }
}

/**
 * Get transaction report by category
 */
function getTransactionReportByCategory($fiscal_year) {
    global $conn;
    
    try {
        $query = "EXEC sp_GetTransactionReportByCategory @FiscalYear = ?";
        $result = sqlsrv_query($conn, $query, array($fiscal_year));
        
        if ($result === false) {
            return array();
        }
        
        $report = array();
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $report[] = $row;
        }
        
        return $report;
        
    } catch (Exception $e) {
        return array();
    }
}

// =====================================================
// APPLICATION REMARKS (Fix processing notes)
// =====================================================

/**
 * Add remark to application
 */
function addApplicationRemark($app_id, $staff_id, $remark, $type = 'Note', $is_internal = true) {
    global $conn;
    
    try {
        $query = "EXEC sp_AddApplicationRemark 
            @ApplicationID = ?,
            @StaffID = ?,
            @RemarkText = ?,
            @RemarkType = ?,
            @IsInternal = ?";
        
        $params = array($app_id, $staff_id, $remark, $type, $is_internal ? 1 : 0);
        $result = sqlsrv_query($conn, $query, $params);
        
        return ($result !== false);
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get application remarks
 */
function getApplicationRemarks($app_id, $show_internal_only = false) {
    global $conn;
    
    try {
        $query = "EXEC sp_GetApplicationRemarks 
            @ApplicationID = ?,
            @ShowInternalOnly = ?";
        
        $params = array($app_id, $show_internal_only ? 1 : 0);
        $result = sqlsrv_query($conn, $query, $params);
        
        if ($result === false) {
            return array();
        }
        
        $remarks = array();
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $remarks[] = $row;
        }
        
        return $remarks;
        
    } catch (Exception $e) {
        return array();
    }
}

// =====================================================
// HELPER FUNCTIONS
// =====================================================

/**
 * Get status badge CSS class
 */
function getStatusBadgeClass($status) {
    $badge_map = array(
        'Pending' => 'badge-warning',
        'Processing' => 'badge-info',
        'Approved' => 'badge-success',
        'Rejected' => 'badge-danger',
        'In Progress' => 'badge-info'
    );
    
    return $badge_map[$status] ?? 'badge-secondary';
}

/**
 * Check if user has permission
 */
function hasPermission($staff_id, $permission_name) {
    global $conn;
    
    try {
        $query = "EXEC sp_HasPermission @StaffID = ?, @PermissionName = ?";
        $result = sqlsrv_query($conn, $query, array($staff_id, $permission_name));
        
        if ($result === false) {
            return false;
        }
        
        $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
        return ($row['HasPermission'] > 0);
        
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get role permissions
 */
function getRolePermissions($role) {
    global $conn;
    
    try {
        $query = "EXEC sp_GetRolePermissions @Role = ?";
        $result = sqlsrv_query($conn, $query, array($role));
        
        if ($result === false) {
            return array();
        }
        
        $permissions = array();
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $permissions[] = $row;
        }
        
        return $permissions;
        
    } catch (Exception $e) {
        return array();
    }
}

/**
 * Log audit event
 */
function logAuditEvent($user_id, $user_type, $action, $table_name, $record_id, $old_values = null, $new_values = null, $ip_address = null) {
    global $conn;
    
    try {
        if (empty($ip_address)) {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        }
        
        $query = "EXEC sp_LogAuditEvent 
            @UserID = ?,
            @UserType = ?,
            @Action = ?,
            @TableName = ?,
            @RecordID = ?,
            @OldValues = ?,
            @NewValues = ?,
            @IPAddress = ?";
        
        $params = array($user_id, $user_type, $action, $table_name, $record_id, $old_values, $new_values, $ip_address);
        $result = sqlsrv_query($conn, $query, $params);
        
        return ($result !== false);
        
    } catch (Exception $e) {
        return false;
    }
}

?>
