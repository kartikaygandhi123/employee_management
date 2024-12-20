<?php
function checkAccess($requiredRole) {
    session_start();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
        die("Access Denied: You do not have permission to access this page.");
    }
}
?>
