<!--
    PROGRAMMER: Nicholas Domingo
    CREATED: 9/3/26
    DESCRIPTION: Create a laundry order application with user and admin features
-->
<?php
session_start();

unset($_SESSION['username']);
unset($_SESSION['role']);
unset($_SESSION['login_attempts']);

header("Location: login.php");
exit();
