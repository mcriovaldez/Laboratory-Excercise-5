<?php
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/config.php';
requireAdmin();

$productCount = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$orderCount = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$userCount = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$prefix = '../';
$currentPage = '';
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Admin | Online Clothing Store</title><link rel="stylesheet" href="../style.css"></head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main class="page-width page-intro">
        <p class="eyebrow">Admin</p><h1>Admin<br><em>dashboard.</em></h1>
        <div class="admin-grid">
            <div class="quick-panel"><h2><?= $productCount ?></h2><p>Products</p></div>
            <div class="quick-panel"><h2><?= $orderCount ?></h2><p>Orders</p></div>
            <div class="quick-panel"><h2><?= $userCount ?></h2><p>Users</p></div>
        </div>
        <a class="solid-button" href="inventory.php">Manage Inventory</a>
    </main>
</body>
</html>
