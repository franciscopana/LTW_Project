<?php


function logout(PDO $db)
{
    setcookie('sessionID', '', (time() - 3600), '/', httponly:true);

    remove_session($_COOKIE['sessionID'], $db);

    header('Location: /');

}
