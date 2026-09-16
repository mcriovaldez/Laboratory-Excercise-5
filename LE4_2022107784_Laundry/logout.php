<!--
    PROGRAMMER: Nicholas Domingo
    CREATED: 9/3/26
    DESCRIPTION: Create a laundry order application with user and admin features
-->
<?php
session_start();

$_SESSION = array();

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

/*
unset($_SESSION['username']);
unset($_SESSION['role']);
unset($_SESSION['login_attempts']);
*/


header("Location: login.php");
exit();
