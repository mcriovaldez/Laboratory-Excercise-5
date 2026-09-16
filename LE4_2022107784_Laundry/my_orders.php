<?php
/*
    PROGRAMMER: Nicholas Domingo
    CREATED: 9/3/26
    DESCRIPTION: Create a laundry order application with user and admin features
*/
session_start();
require_once 'functions.php';
preventCaching();

// ROLE PROTECTION
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$allOrders = loadOrders();

// show specific user orders only
$myOrders = [];
foreach ($allOrders as $order) {
    if ($order['customer'] === $_SESSION['username']) {$myOrders[] = $order;}
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card wide">
            <h1>MY ORDERS</h1>
            <hr>

            <?php if (empty($myOrders)): ?>
                <p>You have not created any orders yet.</p>
            <?php else: ?>
                <table class="orders-table">
                    <tr>
                        <th>Order ID</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Instructions</th>
                        <th>Final Amount</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Order Status</th>
                    </tr>
                    <?php foreach ($myOrders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['clothing_type']); ?></td>
                            <td><?php echo htmlspecialchars($order['size']); ?></td>
                            <td><?php echo htmlspecialchars($order['instructions']); ?></td>
                            <td><?php echo formatPeso($order['final_amount']); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                            <td>
                                <span class="status <?php echo $order['payment_status'] === 'Fully Paid' ? 'status-paid' : 'status-unpaid'; ?>">
                                    <?php echo htmlspecialchars($order['payment_status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($order['order_status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

            <hr>
            <a class="btn" href="customer.php">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
