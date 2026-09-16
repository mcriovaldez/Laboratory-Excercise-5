<?php

session_start();

// only clear auth-related session data on logout.
// $_SESSION['orders'] is intentionally left alone - it's the only place
// order data lives, and admins need to keep seeing orders placed by
// users who have since logged out. (Calling session_destroy() here wipes
// $_SESSION['orders'] too, which is what was deleting everyone's orders.)
// The Back-button-after-logout issue is instead fixed by sending
// no-cache headers on every protected page - see preventCaching()
// in functions.php - so the browser can't show a stale cached copy.
unset($_SESSION['username']);
unset($_SESSION['role']);
unset($_SESSION['login_attempts']);

header("Location: login.php");
exit();
