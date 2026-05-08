<?php
/**
 * Database Configuration File
 * SQL Server Connection using SQLSRV Extension
 */

// SQL Server Connection Details
$serverName = "143.198.82.85,1433"; // IP address and port of the SQL Server
$connectionOptions = array(
    "Database" => "BarangayEServices",
    "UID" => "sa",                      // Use the 'sa' username
    "PWD" => 'fFbdRW@gFd67HtE',         // Use your actual password
    "TrustServerCertificate" => true,   // Mandatory for ODBC Driver 18
    "Authentication" => "SqlPassword"   // Forces SQL login instead of SSPI
);

// Create connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

?>
