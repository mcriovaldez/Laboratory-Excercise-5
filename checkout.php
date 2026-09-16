<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/config.php';
requireLogin();

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$error = '';
$address = '';

function loadCheckoutItems(PDO $pdo): array
{
    $items = [];
    foreach ($_SESSION['cart'] ?? [] as $cartItem) {
        $stmt = $pdo->prepare('SELECT product_id, name, price, stock FROM products WHERE product_id = :id');
        $stmt->execute(['id' => (int)$cartItem['product_id']]);
        $product = $stmt->fetch();
        if ($product) {
            $quantity = max(1, (int)$cartItem['quantity']);
            $items[] = ['product' => $product, 'size' => $cartItem['size'], 'quantity' => $quantity];
        }
    }
    return $items;
}

$items = loadCheckoutItems($pdo);
$total = array_reduce($items, fn($sum, $item) => $sum + ((float)$item['product']['price'] * $item['quantity']), 0.0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $address = trim($_POST['shipping_address'] ?? '');

    if (mb_strlen($address) < 10 || mb_strlen($address) > 250) {
        $error = 'Enter a valid shipping address.';
    } else {
        try {
            $pdo->beginTransaction();

            // Re-read prices and stock inside the transaction.
            $freshItems = loadCheckoutItems($pdo);
            $freshTotal = 0.0;

            foreach ($freshItems as $item) {
                if ($item['quantity'] > (int)$item['product']['stock']) {
                    throw new RuntimeException('Not enough stock for one or more items.');
                }
                $freshTotal += (float)$item['product']['price'] * $item['quantity'];
            }

            $stmt = $pdo->prepare('INSERT INTO orders (user_id, total_amount, shipping_address, status) VALUES (:user_id, :total, :address, :status)');
            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'total' => $freshTotal,
                'address' => $address,
                'status' => 'Pending',
            ]);
            $orderId = (int)$pdo->lastInsertId();

            $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, size, quantity, unit_price) VALUES (:order_id, :product_id, :size, :quantity, :unit_price)');
            $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - :quantity WHERE product_id = :product_id AND stock >= :quantity');

            foreach ($freshItems as $item) {
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['product']['product_id'],
                    'size' => $item['size'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['product']['price'],
                ]);
                $stockStmt->execute(['quantity' => $item['quantity'], 'product_id' => $item['product']['product_id']]);
                if ($stockStmt->rowCount() !== 1) {
                    throw new RuntimeException('Stock changed. Please review your bag.');
                }
            }

            $pdo->commit();
            $_SESSION['cart'] = [];
            header('Location: orders.php?placed=1');
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $e instanceof RuntimeException ? $e->getMessage() : 'Order could not be placed.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Online Clothing Store</title><link rel="stylesheet" href="style.css">
</head>
<body>
    <?php $currentPage = ''; include __DIR__ . '/includes/header.php'; ?>
    <main class="page-width page-intro">
        <p class="eyebrow">Checkout</p><h1>Place<br><em>order.</em></h1>
        <?php if ($error !== ''): ?><p class="form-error"><?= e($error) ?></p><?php endif; ?>
        <form class="auth-card checkout-card" method="post" action="checkout.php">
            <?= csrfField() ?>
            <label for="shipping_address">Shipping address</label>
            <textarea id="shipping_address" name="shipping_address" maxlength="250" required><?= e($address) ?></textarea>
            <p><strong>Total:</strong> ₱<?= number_format($total, 2) ?></p>
            <button class="solid-button full" type="submit">Place Order</button>
        </form>
    </main>
</body>
</html>
