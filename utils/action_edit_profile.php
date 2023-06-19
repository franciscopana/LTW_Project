<?php

declare(strict_types=1);

require_once __DIR__.'/../database/client.db.php';
require_once 'utils/hash.php';


function edit_profile(Client $oldUser,
    $newEmail,
    $newDisplayName,
    $newImage,
    PDO $db
) : bool {
    $newUsername = $oldUser->username;
    if ($newUsername !== null && $newUsername !== $oldUser->username) {
        if (user_already_exists($newUsername, $db) === true) {
            return false;
        }
    }

    if ($newImage['tmp_name'] !== '') {
        $newImageContent = file_get_contents($newImage['tmp_name']);
        if (is_dir(__DIR__.'/../user_data/profile_pictures/') === false) {
            log_to_stdout("profile_pictures directory doesnt exist yet... creating");
            mkdir(__DIR__.'/../user_data/profile_pictures/', 0777, true);
        }

        if ($oldUser->image === Client::DEFAULT_IMAGE) {
            $newImageName     = hash_profile_picture($newUsername).'.png';
            $newImageLocation = __DIR__.'/../user_data/profile_pictures/'.$newImageName;
            file_put_contents($newImageLocation, $newImageContent);
            $newImage = '/user_data/profile_pictures/'.$newImageName;
        } else {
            file_put_contents(__DIR__.'/../'.$oldUser->image, $newImageContent);
            $newImage = $oldUser->image;
        }
    } else {
        $newImage = $oldUser->image;
    }

    $newUser = new Client($newUsername, $newEmail, $oldUser->password, $newDisplayName, $newImage, $oldUser->createdAt);
    return edit_user($newUser, $db);

}


function edit_password(Client $client, string $currentPassword, string $newPassword1, string $newPassword2, PDO $db) : bool
{
    if (verify_password($currentPassword, $client->password) === false) {
        return false;
    }

    if ($newPassword1 !== $newPassword2) {
        return false;
    };

    return change_password($client->username, $newPassword1, $db);

}
