<?php
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/config.php';
requireAdmin();

$message = '';
$categories = allowedCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);

    if (!$productId || $stock === false || $stock < 0 || $price === false || $price < 0) {
        $message = 'Invalid inventory values.';
    } else {
        $stmt = $pdo->prepare('UPDATE products SET stock = :stock, price = :price WHERE product_id = :id');
        $stmt->execute(['stock' => $stock, 'price' => $price, 'id' => $productId]);
        $message = 'Inventory updated.';
    }
}

$products = $pdo->query('SELECT product_id, name, category, price, stock FROM products ORDER BY product_id')->fetchAll();
$prefix = '../';
$currentPage = '';
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Inventory | Online Clothing Store</title><link rel="stylesheet" href="../style.css"></head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main class="page-width page-intro">
        <p class="eyebrow">Admin</p><h1>Manage<br><em>inventory.</em></h1>
        <?php if ($message !== ''): ?><p class="notice-inline"><?= e($message) ?></p><?php endif; ?>
        <div class="data-table-wrap"><table class="data-table"><thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Update</th></tr></thead><tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= e($product['name']) ?></td><td><?= e($product['category']) ?></td>
                <td colspan="3">
                    <form class="inventory-form" method="post" action="inventory.php">
                        <?= csrfField() ?><input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                        <input type="number" name="price" min="0" step="0.01" value="<?= e((string)$product['price']) ?>" required>
                        <input type="number" name="stock" min="0" value="<?= (int)$product['stock'] ?>" required>
                        <button class="text-button" type="submit">Save</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
    </main>
</body>
</html>
