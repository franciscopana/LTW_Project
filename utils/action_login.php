<?php

declare(strict_types=1);

require_once __DIR__.'/../utils/hash.php';
require_once __DIR__.'/../database/client.db.php';
require_once __DIR__.'/../utils/session.php';


function login(string $username, string $password, PDO $db) : bool
{
    $client = get_user($username, $db);
    if ($client === null) {
        return false;
    }

    if (verify_password($password, $client->password) === false) {
        return false;
    }

    $token   = bin2hex(random_bytes(128));
    $session = new Session($username, $token, time());

    if (create_new_session($session, $db) === false) {
        return false;
    }

    set_cookie_session($session);
    return true;

}
