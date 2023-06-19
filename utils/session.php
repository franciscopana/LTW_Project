<?php

declare(strict_types=1);

require_once __DIR__.'/../database/session.db.php';
require_once __DIR__."/../database/client.db.php";

$expiryTime = (60 * 60 * 24);


function set_cookie_session(Session $session)
{
    global $expiryTime;
    setcookie(
        'sessionID',
        $session->token,
        [
            'expires'  => (time() + $expiryTime),
            'path'     => '/',
            'httponly' => true,
            "samesite" => "Strict",
        ]
    );

}


function is_session_valid(PDO $db) : ?Session
{
    global $expiryTime;
    if (isset($_COOKIE['sessionID']) === false) {
        return null;
    }

    $session = get_session($_COOKIE['sessionID'], $db);
    if ($session === null) {
        setcookie('sessionID', '', (time() - 3600), '/', httponly:true);
        return null;
    }

    if ($session->lastUsed->add(new DateInterval('PT'.$expiryTime.'S')) > new DateTime('now') === false) {
        remove_session($session->token, $db);
        return null;
    }

    if (is_user_password_invalidated($session->username, $db) === true && $_SERVER["REQUEST_URI"] !== "/resetPassword") {
        header("Location: /resetPassword");
        exit();
    }

    return $session;

}
