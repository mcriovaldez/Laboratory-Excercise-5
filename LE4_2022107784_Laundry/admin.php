<?php
/*
    PROGRAMMER: Nicholas Domingo
    CREATED: 9/3/26
    DESCRIPTION: Create a laundry order application with user and admin features
*/
session_start();
require_once 'functions.php';
preventCaching();

//role protection
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] !== 'admin') {
    ?>
    <!DOCTYPE html>
    <html><head><title>Access Denied</title><link rel="stylesheet" href="style.css"></head>
    <body><div class="container"><div class="card">
        <h2>Access Denied</h2>
        <a class="btn" href="login.php">Back to Login</a>
    </div></div></body></html>
    <?php
    exit();
}

$orders = loadOrders();

$processMessage = $_SESSION['process_message'] ?? '';
unset($_SESSION['process_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card wide">
            <h1>ADMIN DASHBOARD</h1>
            <hr>
            <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
            <p>Role: Administrator</p>

            <?php if (!empty($processMessage)): ?>
                <p class="notice"><?php echo htmlspecialchars($processMessage); ?></p>
            <?php endif; ?>

            <h2>Laundry Orders</h2>
            <?php if (empty($orders)): ?>
                <p>No laundry orders have been submitted yet.</p>
            <?php else: ?>
                <table class="orders-table">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Order Status</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['customer']); ?></td>
                            <td><?php echo htmlspecialchars($order['clothing_type']); ?></td>
                            <td><?php echo htmlspecialchars($order['size']); ?></td>
                            <td><?php echo formatPeso($order['final_amount']); ?></td>
                            <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                            <td>
                                <span class="status <?php echo $order['payment_status'] === 'Fully Paid' ? 'status-paid' : 'status-unpaid'; ?>">
                                    <?php echo htmlspecialchars($order['payment_status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($order['order_status']); ?></td>
                            <td>
                                <?php if ($order['order_status'] === 'Forwarded to Washing Area'): ?>
                                    <span class="status status-paid">Forwarded</span>
                                <?php elseif ($order['payment_status'] === 'Fully Paid'): ?>
                                    <form method="POST" action="process_order.php" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                        <button type="submit" class="btn btn-small btn-primary">PROCESS ORDER</button>
                                    </form>
                                <?php else: ?>
                                    <span class="status status-unpaid">Awaiting Payment</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>

            <hr>
            <a class="btn btn-logout" href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>
