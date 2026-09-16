<!--
    PROGRAMMER: Nicholas Domingo
    CREATED: 9/3/26
    DESCRIPTION: Create a laundry order application with user and admin features
-->
<?php
session_start();

// user array
if (!defined('USERS')) {
    define('USERS', 
    [
        ["username" => "john", "password" => "123",  "role" => "user"],
        ["username" => "jane",  "password" => "456",  "role" => "user"],
        ["username" => "juan",  "password" => "789",  "role" => "user"],
        ["username" => "admin", "password" => "admin123", "role" => "admin"]
    ]);
}

//COOKIE IMPLEMENTATION
define('REMEMBER_COOKIE_NAME', 'remembered_username');
define('REMEMBER_COOKIE_DAYS', 30);

$errorMessage = "";

// track login attempts and refresh after 3
if (!isset($_SESSION['login_attempts'])) {$_SESSION['login_attempts'] = 0;}

// pre-fill username from cookie (if the user checked "Remember me" before)
$rememberedUsername = $_COOKIE[REMEMBER_COOKIE_NAME] ?? '';
$rememberChecked = $rememberedUsername !== '';

// send to dashboard if already logged
if (isset($_SESSION['username']) && isset($_SESSION['role'])) 
{
    if ($_SESSION['role'] === 'admin') {header("Location: admin.php");}
    else {header("Location: customer.php");}
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredUsername = trim($_POST['username'] ?? '');
    $enteredPassword = trim($_POST['password'] ?? '');
    $rememberMe      = isset($_POST['remember']); // checkbox: present only when checked

    // keep the checkbox/username reflecting what was just submitted if login fails
    $rememberChecked    = $rememberMe;
    $rememberedUsername = $enteredUsername;

    $authenticatedUser = null;

    foreach (USERS as $account) {
        if ($account['username'] === $enteredUsername && $account['password'] === $enteredPassword) {
            $authenticatedUser = $account;
            break;
        }
    }

    if ($authenticatedUser !== null) {
        //login success
        $_SESSION['username']      = $authenticatedUser['username'];
        $_SESSION['role']          = $authenticatedUser['role'];
        $_SESSION['login_attempts'] = 0;

        // handle "remember me" cookie
        if ($rememberMe) {
            // store the username in a cookie for REMEMBER_COOKIE_DAYS days
            setcookie(
                REMEMBER_COOKIE_NAME,
                $authenticatedUser['username'],
                [
                    'expires'  => time() + (REMEMBER_COOKIE_DAYS * 24 * 60 * 60),
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );
        } else {
            // checkbox unchecked -> clear any previously remembered username
            setcookie(
                REMEMBER_COOKIE_NAME,
                '',
                [
                    'expires'  => time() - 3600,
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );
        }

        if ($_SESSION['role'] === 'admin') {header("Location: admin.php");}
        else {header("Location: customer.php");}
        exit();
    } 
    else 
    { 
        //login failed
        $_SESSION['login_attempts']++;

        //refresh after 3 attempts
        if ($_SESSION['login_attempts'] >= 3) {
            $errorMessage = "Max attempts reached, session ended.";
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['login_attempts'] = 0;
        } else {
            $remaining = 3 - $_SESSION['login_attempts'];
            $errorMessage = "Invalid username or password. Please try again. ($remaining attempt(s) remaining)";
        }
    }
}
?>

<!--HTML SECTION--> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laundry Service System - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card login-box">
            <h1>LAUNDRY SERVICE SYSTEM</h1>
            <hr>

            <?php if (!empty($errorMessage)): ?>
                <p class="error-message"><?php echo htmlspecialchars($errorMessage); ?></p>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username"
                       value="<?php echo htmlspecialchars($rememberedUsername); ?>" required
                       <?php echo $rememberedUsername === '' ? 'autofocus' : ''; ?>>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required
                       <?php echo $rememberedUsername !== '' ? 'autofocus' : ''; ?>>

                <div class="checkbox-row">
                    <label>
                        <input type="checkbox" id="remember" name="remember"
                               <?php echo $rememberChecked ? 'checked' : ''; ?>>
                        Remember my username
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">LOGIN</button>
            </form>

            <hr>
            <p>
                Sample accounts:<br>
                Regular user: john/123 jane/456 juan/789 <br>
                Administrator: admin/admin123
            </p>
        </div>
    </div>
</body>
</html>
