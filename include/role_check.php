<?php
/*
* Programmer: Jordan Christopher D. Advincula
* Date Created: September 16, 2026
* Description: Role Based Access Control Implementation
*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure session check has already validated login
if (!isset($_SESSION['role'])) {
    header("Location: ../login.php?error=" . urlencode("Authentication required."));
    exit();
}

// Verify that the required role variable is set on the target page
if (isset($required_role)) {
    // If user's role does not match the required role, block access
    if ($_SESSION['role'] !== $required_role) {
        header("Location: ../unauthorized.php");
        exit();
    }
}
?>