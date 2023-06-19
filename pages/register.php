<?php
require_once __DIR__.'/../utils/action_register.php';
require_once __DIR__.'/../utils/session.php';
require_once __DIR__.'/../database/database.php';

$db = get_database();

if (is_session_valid($db) !== null) {
    header('Location: /');
    exit();
}

$registered = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'];
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];

    //Cannot register an username that is me due to the api /clients/me returning the user logged on
    if ($username !== "me") {
        $registered = register($name, $username, $email, $password, $db);
    }

    if ($registered === true) {
        header('Location: /');
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/layout.css" rel="stylesheet" type="text/css">
    <link href="css/login_register.css" rel="stylesheet" type="text/css">
    <link href="css/components.css" rel="stylesheet" type="text/css">
    <script src="/js/password.js"></script>
</head>
<body>
    <div class="container">
        <div class="left">
            <img src="assets/images/illustration.svg" alt="illustration">
        </div>
        <div class="right">
            <h1>Create a new account</h1>
            <p>Please complete the fields below to create an account.</p>

            <form method="post" action="register">
                
                <label for="name">
                    <p>Name</p>
                </label>
                <input type="text" id="name" name="name" required autocomplete="off">

                <label for="username">
                    <p>Username</p>
                </label>
                <input type="text" id="username" name="username" autocomplete="off" required pattern="^[a-zA-Z0-9_\-\.]{3,20}$">

                <label for="email">
                    <p>Email</p>
                </label>
                <input type="text" id="email" name="email" required>

                <label for="password">
                    <p>Password</p>
                </label>
                <input type="password" id="password" name="password" required>

                <label for="password">
                    <p>Confirm password</p>
                </label>
                <input type="password" id="confirmPassword" name="password" required>
                <br>

                <input type="checkbox" onclick="showPasswordsRegister()">
                <label>Show password</label>
        
                <br><br>
                <input type="submit" class="primary" value="Create account" onclick="passwordsMatch('password', 'confirmPassword')">
            </form>

            <?php if ($registered === false &&  $_SERVER['REQUEST_METHOD'] === 'POST') : ?>
                <p class="error">
                    Username or email has already been taken.
                </p>
            <?php endif; ?>

            <p>If you already have an account, <a href="/login">sign in here</a>.</p>
        </div>
    </div>
</body>
</html>
