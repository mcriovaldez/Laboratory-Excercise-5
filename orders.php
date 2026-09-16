<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/config.php';
requireLogin();

$stmt = $pdo->prepare('SELECT order_id, total_amount, status, created_at FROM orders WHERE user_id = :user_id ORDER BY created_at DESC');
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders | Online Clothing Store</title><link rel="stylesheet" href="style.css">
</head>
<body>
    <?php $currentPage = ''; include __DIR__ . '/includes/header.php'; ?>
    <main class="page-width page-intro">
        <p class="eyebrow">Account</p><h1>Your<br><em>orders.</em></h1>
        <?php if (isset($_GET['placed'])): ?><p class="notice-inline">Order placed successfully.</p><?php endif; ?>
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Total</th></tr></thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr><td>#<?= (int)$order['order_id'] ?></td><td><?= e($order['created_at']) ?></td><td><?= e($order['status']) ?></td><td>₱<?= number_format((float)$order['total_amount'], 2) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$orders): ?><tr><td colspan="4">No orders yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
