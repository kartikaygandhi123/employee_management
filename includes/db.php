<?php
$host = 'localhost';
$user = 'u754798798_ems_user';
$password = '#Pgckot999';
$dbname = 'u754798798_ems';

$conn = new mysqli($host, $user, $password, $dbname,3360);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
