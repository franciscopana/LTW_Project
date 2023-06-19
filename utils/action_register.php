<?php
declare(strict_types=1);

require_once __DIR__.'/../utils/hash.php';
require_once __DIR__.'/../database/client.db.php';


function register(string $name, string $username, string $email, string $password, PDO $db) : bool
{
    if (user_already_exists($username, $db) === true) {
        return false;
    }

    $hash   = hash_password($password);
    $client = new Client($username, $email, $hash, $name, Client::DEFAULT_IMAGE, time());
    if (insert_new_client($client, $db) === false) {
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
