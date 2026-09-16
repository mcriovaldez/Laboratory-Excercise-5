<?php
/*
* Programmer: Jordan Christopher D. Advincula
* Date Created: September 16, 2026
* Description: Checks user session - automatically logs out and destroy the session on inactivity
*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$timeout_duration = 900; // 15 minutes = 900 seconds

// 1. Check if user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php?error=" . urlencode("Please login to access this page."));
    exit();
}

// 2. Check for session inactivity timeout
if (isset($_SESSION['last_activity'])) {
    $elapsed_time = time() - $_SESSION['last_activity'];
    
    if ($elapsed_time >= $timeout_duration) {
        // Session expired: Clear all variables and destroy session
        session_unset();
        session_destroy();
        
        // Redirect to login with timeout notice
        header("Location: ../login.php?error=" . urlencode("Your session has expired due to inactivity. Please login again."));
        exit();
    }
}

// 3. Update last activity timestamp for active sessions
$_SESSION['last_activity'] = time();
?>