<!--
    PROGRAMMER: Nicholas Domingo
    CREATED: 9/3/26
    DESCRIPTION: Create a laundry order application with user and admin features
-->
<?php
session_start();

// ROLE PROTECTION
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] !== 'user') {
    ?>
    <!DOCTYPE html>
    <html><head><title>Access Denied</title><link rel="stylesheet" href="style.css"></head>
    <body><div class="container"><div class="card">
        <h2>Access Denied.</h2>
        <p>This page is for regular users only.</p>
        <a class="btn" href="login.php">Back to Login</a>
    </div></div></body></html>
    <?php
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>CUSTOMER DASHBOARD</h1>
            <hr>
            <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
            <p>Role: Regular User</p>

            <div class="menu">
                <a class="btn btn-primary" href="clothing_order.php">Create Laundry Order</a>
                <a class="btn btn-primary" href="my_orders.php">View My Orders</a>
                <a class="btn btn-logout" href="logout.php">Logout</a>
            </div>
            <hr>
        </div>
    </div>
</body>
</html>
