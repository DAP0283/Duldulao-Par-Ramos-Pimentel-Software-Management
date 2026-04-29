<?php
session_start();
require_once('../../includes/db_config.php');

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $new_status = ($_GET['action'] === 'activate') ? 1 : 0;

    $sql = "UPDATE Clients SET IsActive = ?, UpdatedAt = GETDATE() WHERE ClientID = ?";
    $params = array($new_status, $id);
    
    if (sqlsrv_query($conn, $sql, $params)) {
        header("Location: users.php?msg=User status updated");
    } else {
        die(print_r(sqlsrv_errors(), true));
    }
}
?>