<?php
    require_once 'components/navbar/navbar.php';
    require_once 'database/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="/css/layout.css">
    <link rel="stylesheet" href="/css/theme.css">
    <link rel="stylesheet" href="/css/remixicon.css">
    <link rel="stylesheet" href="/css/home.css">
    <script src="/js/home.js"></script>
</head>
<body>

<?php
    $db = get_database();

    $session = is_session_valid($db);

    navbar($db);

    $sign = '<div class = "sign">
                <a href="/login">Sign in</a>
                <a href="/register">Sign up for free</a>
            </div>';
?>
<main>
    <header>
        <?php if ($session === null) {
            echo $sign;
        } ?>
        <h1>Aura Tickets</h1>
        <h2>The easiest way to manage your work</h2>
    </header>
    <section>
        <h2>Unlimited Benefits for your team</h2>
        <div class="slider-container">
            <ul class="slider">
                <li class="active">
                    <div class="card">
                        <img src="assets/images/landing1.svg" alt="Image 1">
                        <p>Streamline your ticket management process</p>
                    </div>
                </li>
                <li>
                    <div class="card">
                        <img src="assets/images/landing2.svg" alt="Image 2">
                        <p>Track and resolve tickets with efficiency</p>
                    </div>
                </li>
                <li>
                    <div class="card">
                        <img src="assets/images/landing3.svg" alt="Image 3">
                        <p>Gain insights into ticket history and progress</p>
                    </div>
                </li>
                <li>
                    <div class="card">
                        <img src="assets/images/landing4.svg" alt="Image 4">
                        <p>Engage and conquer project tasks with ease</p>
                    </div>
                </li>
                <li>
                    <div class="card">
                        <img src="assets/images/landing5.svg" alt="Image 5">
                        <p>Manage your team's departments</p>
                    </div>
                </li>
                <li>
                    <div class="card">
                        <img src="assets/images/landing6.svg" alt="Image 6">
                        <p>Manage your team's members</p>
                    </div>
                </li>
            </ul>
            <div class="arrows">
                <span class="prev no-select" onclick="showPrevious()">&#8249;</span>
                <span class="next no-select" onclick="showNext()">&#8250;</span>
            </div>
        </div>
    </section>
</main>
    
</body>
</html>
