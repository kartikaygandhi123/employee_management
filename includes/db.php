<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'employee_management';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}