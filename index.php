<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/functions.php';

$currentPage = 'home';
$preferredCategory = $_COOKIE['preferred_category'] ?? '';
if (!in_array($preferredCategory, allowedCategories(), true)) {
    $preferredCategory = '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Online Clothing Store">
    <title>Online Clothing Store</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main>
        <section class="hero-home page-width">
            <div class="hero-copy">
                <p class="eyebrow">Online Clothing Store</p>
                <h1>[Main<br><em>Heading]</em></h1>
                <p class="hero-lede">[Short homepage description]</p>
                <a class="solid-button" href="dashboard.php">Shop</a>
            </div>

            <aside class="quick-panel" aria-label="Shopping shortcuts">
                <p class="eyebrow">Start here</p>
                <h2>Shop by<br><em>category.</em></h2>
                <div class="quick-links">
                    <a href="dashboard.php">All products <span>→</span></a>
                    <a href="dashboard.php?category=T-Shirts">T-Shirts <span>→</span></a>
                    <a href="dashboard.php?category=Hoodies">Hoodies <span>→</span></a>
                    <a href="dashboard.php?category=Pants">Pants <span>→</span></a>
                    <?php if ($preferredCategory !== ''): ?>
                        <a href="dashboard.php?category=<?= urlencode($preferredCategory) ?>">Recently viewed:
                            <?= e($preferredCategory) ?> <span>→</span></a>
                    <?php endif; ?>
                </div>
            </aside>
        </section>

        <section class="marquee"><span>ONLINE STORE</span><span>ONLINE STORE</span><span>ONLINE STORE</span></section>

        <section class="statement page-width">
            <div>
                <p class="eyebrow">About us</p>
                <h2>[About<br><em>Heading]</em></h2>
            </div>
            <p>[About description goes here]</p>
        </section>
    </main>

    <footer class="site-footer page-width">
        <p>Online Clothing Store &copy; 2026</p>
    </footer>
</body>

</html>