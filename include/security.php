<?php
/*
* Programmer: Jordan Christopher D. Advincula
* Date Created: September 16, 2026
* Description: Security measures to prevent direct file access and XSS Injection
*/
// Prevent direct file access
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    exit('Direct access not permitted.');
}

/**
 * Sanitizes input string to prevent Cross-Site Scripting (XSS)
 */
function sanitize_input($data) {
    if (is_null($data)) return '';
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>