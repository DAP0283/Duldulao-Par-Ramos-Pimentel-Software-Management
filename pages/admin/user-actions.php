<?php
session_start();
require_once('../../includes/db_config.php');

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    if ($_GET['action'] === 'remove') {
        // Start transaction for atomic operation
        sqlsrv_begin_transaction($conn);
        
        try {
            // First delete all applications for this user
            $delete_apps_sql = "DELETE FROM Applications WHERE ClientID = ?";
            $delete_apps_stmt = sqlsrv_prepare($conn, $delete_apps_sql, array($id));
            if (!$delete_apps_stmt || !sqlsrv_execute($delete_apps_stmt)) {
                throw new Exception("Failed to delete user applications");
            }
            
            // Then delete the user
            $delete_user_sql = "DELETE FROM Clients WHERE ClientID = ?";
            $delete_user_stmt = sqlsrv_prepare($conn, $delete_user_sql, array($id));
            if (!$delete_user_stmt || !sqlsrv_execute($delete_user_stmt)) {
                throw new Exception("Failed to delete user");
            }
            
            // Commit transaction
            sqlsrv_commit($conn);
            header("Location: users.php?msg=User and all associated applications removed successfully");
            exit();
            
        } catch (Exception $e) {
            // Rollback transaction on error
            sqlsrv_rollback($conn);
            die("Error removing user: " . $e->getMessage());
        }
    } else {
        // Handle activate/deactivate actions
        $new_status = ($_GET['action'] === 'activate') ? 1 : 0;
        $sql = "UPDATE Clients SET IsActive = ?, UpdatedAt = GETDATE() WHERE ClientID = ?";
        $params = array($new_status, $id);
        
        if (sqlsrv_query($conn, $sql, $params)) {
            header("Location: users.php?msg=User status updated");
        } else {
            die(print_r(sqlsrv_errors(), true));
        }
    }
}
?>