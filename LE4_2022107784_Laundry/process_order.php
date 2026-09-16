<?php

session_start();

// admin role protection
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    $orders  = $_SESSION['orders'] ?? [];

    foreach ($orders as $index => $order) {
        if ($order['order_id'] === $orderId) {
            if ($order['payment_status'] === 'Fully Paid' && $order['order_status'] === 'Order Received') {
                $_SESSION['orders'][$index]['order_status'] = 'Forwarded to Washing Area';
                $_SESSION['process_message'] =
                    "Laundry Order {$orderId} has been processed and forwarded to the washing area.";
            } else {
                $_SESSION['process_message'] =
                    "Order {$orderId} cannot be processed. It must be fully paid and not already processed.";
            }
            break;
        }
    }
}

header("Location: admin.php");
exit();
