<?php
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin(): void
{
    if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Access denied. Admin account required.');
    }
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token']) . '">';
}

function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Invalid request token. Please refresh the page and try again.');
    }
}

function cartCount(): int
{
    $count = 0;
    foreach ($_SESSION['cart'] ?? [] as $item) {
        $count += (int)($item['quantity'] ?? 0);
    }
    return $count;
}

function allowedCategories(): array
{
    return ['T-Shirts', 'Hoodies', 'Pants', 'Jackets', 'Shorts', 'Accessories'];
}

function allowedSizes(): array
{
    return ['S', 'M', 'L', 'XL'];
}
