<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/config.php';

$currentPage = 'cart';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $key = $_POST['cart_key'] ?? '';

    if (($_POST['action'] ?? '') === 'remove') {
        unset($_SESSION['cart'][$key]);
        $message = 'Item removed.';
    }

    if (($_POST['action'] ?? '') === 'update') {
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        if (isset($_SESSION['cart'][$key]) && $quantity !== false && $quantity >= 1 && $quantity <= 99) {
            $_SESSION['cart'][$key]['quantity'] = $quantity;
            $message = 'Quantity updated.';
        } else {
            $message = 'Quantity must be between 1 and 99.';
        }
    }
}

$entries = [];
$subtotal = 0.0;
foreach ($_SESSION['cart'] ?? [] as $key => $cartItem) {
    $stmt = $pdo->prepare('SELECT product_id, name, category, price, stock, color FROM products WHERE product_id = :id');
    $stmt->execute(['id' => (int)$cartItem['product_id']]);
    $product = $stmt->fetch();
    if (!$product) {
        continue;
    }

    $quantity = max(1, (int)$cartItem['quantity']);
    $lineTotal = (float)$product['price'] * $quantity;
    $subtotal += $lineTotal;
    $entries[] = ['key' => $key, 'product' => $product, 'size' => $cartItem['size'], 'quantity' => $quantity, 'line_total' => $lineTotal];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Bag | Online Clothing Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="cart-page page-width">
        <div class="page-intro compact"><p class="eyebrow">Bag</p><h1>Your bag</h1></div>
        <?php if ($message !== ''): ?><p class="notice-inline"><?= e($message) ?></p><?php endif; ?>

        <div class="cart-layout">
            <section class="cart-list" aria-live="polite">
                <?php if (!$entries): ?>
                    <div class="empty-state"><span class="empty-mark">+</span><h2>Empty bag</h2><p>Add a product.</p><a class="solid-button" href="dashboard.php">Shop</a></div>
                <?php endif; ?>

                <?php foreach ($entries as $entry): ?>
                    <article class="cart-item">
                        <div class="product-art mini <?= e($entry['product']['color']) ?>"><span><?= e($entry['size']) ?></span></div>
                        <div class="cart-item-info">
                            <p class="eyebrow"><?= e($entry['product']['category']) ?></p>
                            <h3><?= e($entry['product']['name']) ?></h3>
                            <p>₱<?= number_format((float)$entry['product']['price'], 2) ?> each</p>
                            <form method="post" action="cart.php">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="cart_key" value="<?= e($entry['key']) ?>">
                                <button class="text-button" type="submit">Remove</button>
                            </form>
                        </div>
                        <form method="post" action="cart.php">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="cart_key" value="<?= e($entry['key']) ?>">
                            <label class="quantity-label">Qty
                                <input class="quantity" name="quantity" type="number" min="1" max="99" value="<?= (int)$entry['quantity'] ?>">
                            </label>
                            <button class="text-button" type="submit">Update</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </section>

            <aside class="cart-summary">
                <?php if ($entries): ?>
                    <div class="summary-head"><h2>Summary</h2><span><?= count($entries) ?> item(s)</span></div>
                    <p><span>Subtotal</span><strong>₱<?= number_format($subtotal, 2) ?></strong></p>
                    <hr>
                    <p class="summary-total"><span>Total</span><strong>₱<?= number_format($subtotal, 2) ?></strong></p>
                    <a class="solid-button full" href="checkout.php">Checkout</a>
                    <small>Prices are recalculated from the database.</small>
                <?php endif; ?>
            </aside>
        </div>
    </main>

    <footer class="site-footer page-width"><p>Online Clothing Store &copy; 2026</p></footer>
</body>
</html>
