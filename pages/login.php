<?php
require_once __DIR__.'/../utils/action_login.php';
require_once __DIR__.'/../database/database.php';
require_once __DIR__.'/../utils/session.php';

$db = get_database();

if (is_session_valid($db) !== null) {
    header('Location: /');
    exit();
}

$loggedIn = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $loggedIn = login($username, $password, $db);
    if ($loggedIn === true) {
        header('Location: /');
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/layout.css" rel="stylesheet" type="text/css">
    <link href="/css/login_register.css" rel="stylesheet" type="text/css">
    <link href="/css/components.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div class="container">
        <div class="left">
            <img src="assets/images/illustration.svg" alt="illustration">
        </div>
        <div class="right">
            <h1>Welcome Back!</h1>
            <p>Please enter your username and password to login.</p>

            <form method="post" action="login">
                <label for="username">
                    <p>Username:</p>
                </label>
                <input type="text" id="username" name="username" autocomplete="off" required>

                <label for="password">
                    <p>Password:</p>
                </label>
                <input type="password" id="password" name="password" required>

                <br>
                <input type="submit" class="primary" value="Login">
            </form>

            <?php if ($loggedIn === false &&  $_SERVER['REQUEST_METHOD'] === 'POST') : ?>
                <p class="error">Invalid username or password.</p>
            <?php endif; ?>
            <p>If you don't have an account, <a href="/register">sign up here</a></p>
        </div>
    </div>
</body>
</html>
