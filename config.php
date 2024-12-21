<?php
// Detect the protocol (http or https)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

// Detect the host (localhost or your domain)
$host = $_SERVER['HTTP_HOST'];

// Set the base path dynamically based on the environment
if ($host === 'localhost') {
    // Local environment: Append the project folder name
    $base_path = '/employee_management/';
} else {
    // Server environment: Root is already employee_management, so no folder name
    $base_path = '/';
}

// Define the base URL
define('BASE_URL', rtrim("$protocol://$host$base_path", '/') . '/');
