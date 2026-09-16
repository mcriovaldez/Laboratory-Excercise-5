<?php
// Secure session cookie settings. Secure is enabled automatically when HTTPS is used.
$usingHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $usingHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Create one CSRF token for the current session.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
