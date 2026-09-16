<?php
/*
    PROGRAMMER: Nicholas Domingo
    CREATED: 9/3/26
    DESCRIPTION: Create a laundry order application with user and admin features
*/
session_start();

// orders now live in data/orders.xml (see functions.php), not $_SESSION,
// so it's safe to fully tear down the session here without losing anyone's data.
$_SESSION = [];

// remove the session cookie itself so the browser can't replay the old session id
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

header("Location: login.php");
exit();
