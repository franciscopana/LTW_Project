<?php
declare(strict_types=1);

require_once __DIR__."/../database/database.php";
require_once __DIR__."/session.php";
require_once __DIR__."/../database/client.db.php";


function is_current_user_agent(PDO $db) : bool
{
    $session = is_session_valid($db);
    if ($session === null) {
        return false;
    }

    $sql = "SELECT COUNT(*) FROM Agents WHERE username=:username";

    $stmt = $db->prepare($sql);
    $stmt->bindParam("username", $session->username, PDO::PARAM_STR);

    $stmt->execute();

    return $stmt->fetchColumn() === 1;

}


function is_current_user_admin(PDO $db) : bool
{
    $session = is_session_valid($db);
    if ($session === null) {
        return false;
    }

    $sql = "SELECT COUNT(*) FROM Admins WHERE username=:username";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(":username", $session->username, PDO::PARAM_STR);

    $stmt->execute();

    return $stmt->fetchColumn() === 1;

}


function promote_to_agent(string $username, PDO $db) : bool
{
    $sql  = "INSERT INTO Agents VALUES (:username);";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":username", $username, PDO::PARAM_STR);

    $stmt->execute();

    return  $stmt->rowCount() === 1;

}


function promote_to_admin(string $username, PDO $db) : bool
{
    $sql  = "INSERT INTO Admins VALUES (:username);";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":username", $username, PDO::PARAM_STR);

    $stmt->execute();

    return  $stmt->rowCount() === 1;

}


function demote_to_agent(string $username, PDO $db) : bool
{
    $sql  = "DELETE FROM Admins WHERE username=:username;";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":username", $username, PDO::PARAM_STR);

    $stmt->execute();

    return $stmt->rowCount() === 1;

}


function demote_to_client(string $username, PDO $db) : bool
{
    $sql  = "DELETE FROM Agents WHERE username=:username;";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":username", $username, PDO::PARAM_STR);

    $stmt->execute();

    return  $stmt->rowCount() === 1;

}
