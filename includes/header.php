<?php
$currentPage = $currentPage ?? '';
$prefix = $prefix ?? '';
?>
<header class="site-header">
    <nav class="site-nav page-width" aria-label="Main navigation">
        <a class="brand" href="<?= $prefix ?>index.php">STORE<span>.</span></a>
        <div class="nav-links">
            <a class="nav-button <?= $currentPage === 'home' ? 'active' : '' ?>" href="<?= $prefix ?>index.php">Home</a>
            <a class="nav-button <?= $currentPage === 'shop' ? 'active' : '' ?>" href="<?= $prefix ?>dashboard.php">Shop</a>
            <a class="nav-button <?= $currentPage === 'cart' ? 'active' : '' ?>" href="<?= $prefix ?>cart.php">Bag <span class="cart-count"><?= cartCount() ?></span></a>
            <?php if (isLoggedIn()): ?>
                <span class="nav-user">Welcome, <?= e($_SESSION['name'] ?? $_SESSION['email'] ?? 'User') ?></span>
                <a class="nav-button" href="<?= $prefix ?>orders.php">Orders</a>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <a class="nav-button" href="<?= $prefix ?>admin/dashboard.php">Admin</a>
                <?php endif; ?>
                <a class="nav-button" href="<?= $prefix ?>logout.php">Logout</a>
            <?php else: ?>
                <a class="nav-button <?= $currentPage === 'login' ? 'active' : '' ?>" href="<?= $prefix ?>login.php">Login</a>
                <a class="nav-button <?= $currentPage === 'register' ? 'active' : '' ?>" href="<?= $prefix ?>register.php">Register</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
