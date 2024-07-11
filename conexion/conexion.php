<?php
$servername = "localhost:3307";
$username = "root";
$password = "";
$dbname = "sis_cont";

global $conn;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>    