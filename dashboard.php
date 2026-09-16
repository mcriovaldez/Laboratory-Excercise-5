<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/config.php';

$currentPage = 'shop';
$categories = allowedCategories();
$sizes = allowedSizes();

// Validate GET filters against strict allowed values.
$category = trim($_GET['category'] ?? 'All');
if ($category !== 'All' && !in_array($category, $categories, true)) {
    $category = 'All';
}

$size = strtoupper(trim($_GET['size'] ?? ''));
if ($size !== '' && !in_array($size, $sizes, true)) {
    $size = '';
}

$search = trim($_GET['search'] ?? '');
if (mb_strlen($search) > 60) {
    $search = mb_substr($search, 0, 60);
}

$minPrice = filter_input(INPUT_GET, 'min_price', FILTER_VALIDATE_FLOAT);
$maxPrice = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT);
$minPrice = ($minPrice !== false && $minPrice !== null && $minPrice >= 0) ? $minPrice : null;
$maxPrice = ($maxPrice !== false && $maxPrice !== null && $maxPrice >= 0) ? $maxPrice : null;

// Remember a safe, non-sensitive browsing preference.
if ($category !== 'All') {
    setcookie('preferred_category', $category, [
        'expires' => time() + 60 * 60 * 24 * 30,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

$message = '';

// Add to cart using a POST request and a CSRF token.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    verifyCsrf();

    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $chosenSize = strtoupper(trim($_POST['size'] ?? ''));

    if (!$productId || !in_array($chosenSize, $sizes, true)) {
        $message = 'Invalid product or size.';
    } else {
        $stmt = $pdo->prepare('SELECT product_id FROM products WHERE product_id = :id AND stock > 0');
        $stmt->execute(['id' => $productId]);

        if (!$stmt->fetch()) {
            $message = 'Product is unavailable.';
        } else {
            $key = $productId . ':' . $chosenSize;
            if (!isset($_SESSION['cart'][$key])) {
                $_SESSION['cart'][$key] = ['product_id' => $productId, 'size' => $chosenSize, 'quantity' => 1];
            } else {
                $_SESSION['cart'][$key]['quantity']++;
            }
            $message = 'Added to your bag.';
        }
    }
}

// Build a prepared query from validated filters.
$sql = 'SELECT product_id, name, category, price, stock, color, sizes FROM products WHERE 1=1';
$params = [];

if ($category !== 'All') {
    $sql .= ' AND category = :category';
    $params['category'] = $category;
}
if ($search !== '') {
    $sql .= ' AND name LIKE :search';
    $params['search'] = '%' . $search . '%';
}
if ($minPrice !== null) {
    $sql .= ' AND price >= :min_price';
    $params['min_price'] = $minPrice;
}
if ($maxPrice !== null) {
    $sql .= ' AND price <= :max_price';
    $params['max_price'] = $maxPrice;
}
if ($size !== '') {
    $sql .= ' AND FIND_IN_SET(:size, sizes)';
    $params['size'] = $size;
}
$sql .= ' ORDER BY product_id ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | Online Clothing Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main>
        <section class="page-intro page-width">
            <p class="eyebrow">Shop</p>
            <h1>Product<br><em>catalog.</em></h1>
            <p>Use the filters below to browse clothing.</p>
        </section>

        <section class="shop-section page-width">
            <?php if ($message !== ''): ?><p class="notice-inline"><?= e($message) ?></p><?php endif; ?>

            <form class="filter-row" method="get" action="dashboard.php">
                <select name="category">
                    <option value="All">All Categories</option>
                    <?php foreach ($categories as $item): ?>
                        <option value="<?= e($item) ?>" <?= $category === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="size">
                    <option value="">Any Size</option>
                    <?php foreach ($sizes as $item): ?>
                        <option value="<?= e($item) ?>" <?= $size === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="search" name="search" maxlength="60" placeholder="Search" value="<?= e($search) ?>">
                <input type="number" name="min_price" min="0" step="0.01" placeholder="Min price" value="<?= $minPrice !== null ? e((string)$minPrice) : '' ?>">
                <input type="number" name="max_price" min="0" step="0.01" placeholder="Max price" value="<?= $maxPrice !== null ? e((string)$maxPrice) : '' ?>">
                <button class="solid-button" type="submit">Filter</button>
            </form>

            <div class="catalog-top"><div><p class="eyebrow">Products</p><h2><?= e($category) ?></h2></div><span class="product-count"><?= count($products) ?> items</span></div>

            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <article class="product-card">
                        <div class="product-art <?= e($product['color']) ?>"><span><?= e($product['category']) ?></span></div>
                        <div class="product-info">
                            <p class="eyebrow"><?= e($product['category']) ?></p>
                            <h3><?= e($product['name']) ?></h3>
                            <strong>₱<?= number_format((float)$product['price'], 2) ?></strong>
                        </div>
                        <form method="post" action="dashboard.php<?= $category !== 'All' ? '?category=' . urlencode($category) : '' ?>">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                            <label class="size-select">Size
                                <select name="size" required>
                                    <?php foreach (explode(',', $product['sizes']) as $productSize): ?>
                                        <option value="<?= e(trim($productSize)) ?>"><?= e(trim($productSize)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <button class="product-action" type="submit">Add to bag <span aria-hidden="true">+</span></button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer class="site-footer page-width"><p>Online Clothing Store &copy; 2026</p></footer>
</body>
</html>
