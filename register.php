<?php
require __DIR__ . '/includes/session.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/config.php';

$currentPage = 'register';
$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || mb_strlen($name) < 2 || mb_strlen($name) > 80) {
        $errors[] = 'Enter a valid full name.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors[] = 'Password must be at least 8 characters and contain a letter and number.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $check = $pdo->prepare('SELECT user_id FROM users WHERE email = :email');
        $check->execute(['email' => $email]);
        if ($check->fetch()) {
            $errors[] = 'That email is already registered.';
        } else {
            $algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
            $hash = password_hash($password, $algorithm);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
            $stmt->execute(['name' => $name, 'email' => $email, 'password_hash' => $hash, 'role' => 'user']);

            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$pdo->lastInsertId();
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = 'user';
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Online Clothing Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="register-page">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="register-shell page-width">
        <section class="login" aria-labelledby="register-title">
            <div class="hader"><h1 id="register-title">Register</h1><p>New account</p></div>

            <?php foreach ($errors as $error): ?><p class="form-error"><?= e($error) ?></p><?php endforeach; ?>

            <form class="register-form" method="post" action="register.php">
                <?= csrfField() ?>
                <input type="text" name="name" maxlength="80" placeholder="Full name" value="<?= e($name) ?>" required>
                <input type="email" name="email" maxlength="150" placeholder="Email address" value="<?= e($email) ?>" required>
                <input type="password" name="password" minlength="8" placeholder="Password" required>
                <input type="password" name="confirm_password" minlength="8" placeholder="Confirm password" required>
                <button type="submit">Register</button>
                <span>Already have an account? <a href="login.php">Sign in</a></span>
            </form>
        </section>
    </main>
</body>
</html>
