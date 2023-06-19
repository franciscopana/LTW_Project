<?php

declare(strict_types=1);

//  todo: make this an env variable
$salt = 'alskdjfhg';


function hash_password(string $text) : string
{
    global $salt;
    return password_hash($salt.$text.$salt, PASSWORD_DEFAULT);

}


function verify_password(string $text, string $hash) : bool
{
    global $salt;
    return password_verify($salt.$text.$salt, $hash);

}


function hash_profile_picture(string $username) : string
{
    return hash('sha256', $username);

}
