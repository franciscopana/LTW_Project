<?php
require_once __DIR__.'/../../utils/session.php';
require_once __DIR__.'/../../utils/roles.php';


require_once __DIR__.'/../../database/client.db.php';


function get_navbar_user(?Client $client, PDO $db) : string
{
    if ($client === null) {
        return '';
    }

    $imageSrc = $client->image;

    return <<<HTML
        <div class="user" onclick="location.href='/profile'">
            <div>
                <img class="avatar" src=$imageSrc alt="user">
                <div>
                    <h3>$client->displayName</h3>
                    <p>@$client->username</p>
                </div>
            </div>
            <a href="/logout" class="logout">
                <i class="ri-logout-box-line"></i>
            </a>
        </div>
    HTML;

}


function get_login_button() : string
{
    return <<<HTML
        <a href="/login" class="login">
            <i class="ri-login-box-line"></i>
            Login
        </a>
    HTML;

}


function navbar(PDO $db)
{

    $isActive = function (string $path) : string {
        $currentPath = explode("/", $_SERVER['REQUEST_URI']);
        $currentPath = $currentPath[(count($currentPath) - 1)];
        if ($path === $currentPath) {
            return 'true';
        } else {
            return 'false';
        };
    };

    $session = is_session_valid($db);
    $client  = null;
    if ($session !== null) {
        $client = get_user($session->username, $db);
        if (file_exists(__DIR__.'/../../'.$client->image) === false) {
            set_default_client_image($client, $db);
            $client = get_user($client->username, $db);
        }
    }
    ?>
        <link rel="stylesheet" type="text/css" href="/components/navbar/navbar.css">

        <input type="checkbox" id="navbar-checkbox"> 
        <label for="navbar-checkbox">
            <i class="ri-menu-line" id="navbar-open"></i>
            <i class="ri-close-line" id="navbar-close"></i>
        </label>

        <nav class="sidebar">
            <div class="inital-sidebar">
                <div class="logo">
                    <h1>
                        <a href="/">Aura Tickets</a>
                    </h1>
                </div>
                <ul>
                    <li data-active="<?php echo htmlspecialchars($isActive("tickets"))?>">
                        <a href="/tickets">
                            <i class="ri-ticket-line"></i>
                            Tickets
                        </a>
                    </li>
                    <li data-active="<?php echo htmlspecialchars($isActive("faq"))?>">
                        <a href="/faq">
                            <i class="ri-question-line"></i>
                            FAQ
                        </a>
                    </li>
                    <?php if (is_current_user_admin($db) === true) :?>
                     <li data-active="<?php echo htmlspecialchars($isActive("admin"))?>">
                        <a href="/admin">
                            <i class="ri-admin-line"></i>
                            Admin settings
                        </a>
                    </li>
                    <?php endif;?>
                </ul>
            </div>
            <?php
            if ($session !== null) {
                echo get_navbar_user($client, $db);
            } else {
                echo get_login_button();
            }
            ?>
        </nav>
    <?php

}
