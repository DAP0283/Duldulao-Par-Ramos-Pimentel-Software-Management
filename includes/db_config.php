<?php
/**
 * Database Configuration File
 * SQL Server Connection using SQLSRV Extension
 */

// SQL Server Connection Details
$serverName = "MSI\SQLEXPRESS";
$connectionOptions = array(
    "Database" => "BarangayEServices",
    "TrustServerCertificate" => true,
    "Authentication" => "ActiveDirectoryIntegrated"
);

// Create connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

?>
