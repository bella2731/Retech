<?php
$servername = "localhost";
$username = "root";      // change if needed
$password = "";          // change if needed
$dbname = "report"; // your DB name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
