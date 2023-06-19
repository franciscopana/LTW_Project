<?php
require_once __DIR__.'/../utils/action_login.php';
require_once __DIR__.'/../database/database.php';
require_once __DIR__.'/../database/client.db.php';


$db      = get_database();
$session = is_session_valid($db);
if ($session === null) {
    header('Location: /login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    change_password($session->username, $password, $db);
    header('Location: /');
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/css/layout.css" rel="stylesheet" type="text/css">
    <link href="/css/components.css" rel="stylesheet" type="text/css">

    <link href="css/login_register.css" rel="stylesheet" type="text/css">
    <link href="css/components.css" rel="stylesheet" type="text/css">
    <script src="/js/validators.js"></script>

</head>
<body>
    <div class="container">
        <div class="left">
            <img src="assets/images/reset_illustration.svg" alt="illustration">
        </div>
        <div class="right">
            <h1>Reset Password</h1>
            <p>It seems that your password has been invalidated by an admin, please reset your password</p>

            <form method="post" action="resetPassword" onsubmit="return passwordValidator();" name="resetPasswordForm">
                <label for="password">
                    <p>Password:</p>
                </label>
                <input type="password" id="password" name="password" required>

                <label for="confirmPassword">
                    <p>Confirm password:</p>
                </label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>

                <br>
                <input type="submit" class="primary" value="Reset" name="submitButton">
            </form>
        </div>
    </div>
</body>
</html>
