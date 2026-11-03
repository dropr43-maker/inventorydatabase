<?php
// Connection details for MAMP
$host = "localhost";
$user = "root";
$password = "root";
$database = "inventory_db";

// show friendly messages on errors instead of crashing the page
mysqli_report(MYSQLI_REPORT_OFF);

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Could not connect to the database: " . mysqli_connect_error());
}
?>