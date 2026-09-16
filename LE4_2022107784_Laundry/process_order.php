<?php
/*
    PROGRAMMER: Nicholas Domingo
    CREATED: 9/3/26
    DESCRIPTION: Create a laundry order application with user and admin features
*/
session_start();
require_once 'functions.php';

// admin role protection
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    $orders  = loadOrders();

    foreach ($orders as $order) {
        if ($order['order_id'] === $orderId) {
            if ($order['payment_status'] === 'Fully Paid' && $order['order_status'] === 'Order Received') {
                updateOrderStatus($orderId, 'Forwarded to Washing Area');
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
