<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/config.php';

$currentPage = 'login';
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter a valid email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT user_id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            header('Location: dashboard.php');
            exit;
        }

        $error = 'Incorrect email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Online Clothing Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="auth-shell page-width">
        <section class="auth-copy"><p class="eyebrow">Login</p><h1>Welcome<br><em>back.</em></h1><p>Enter your details.</p></section>
        <form class="auth-card" method="post" action="login.php">
            <?= csrfField() ?>
            <p class="eyebrow">Member access</p><h2>Sign in</h2>
            <?php if ($error !== ''): ?><p class="form-error"><?= e($error) ?></p><?php endif; ?>
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" maxlength="150" required value="<?= e($email) ?>" placeholder="you@example.com">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required placeholder="Password">
            <button class="solid-button full" type="submit">Continue</button>
            <a class="account-link" href="register.php">Register</a>
        </form>
    </main>
</body>
</html>
