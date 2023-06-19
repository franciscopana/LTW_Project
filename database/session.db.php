<?php

declare(strict_types=1);

class Session
{

    public string $username;

    public string $token;

    public DateTime $lastUsed;


    public function __construct(string $_username, string $_token, int $_epoch)
    {
        $this->username = $_username;
        $this->token    = $_token;
        $this->lastUsed = new DateTime("@".$_epoch);

    }


}


function create_new_session(Session $session, PDO $db) : bool
{
    $deleteSql  = "DELETE FROM Sessions WHERE user = :username";
    $deleteStmt = $db->prepare($deleteSql);
    $deleteStmt->bindParam(':username', $session->username, PDO::PARAM_STR);
    $deleteStmt->execute();
    $sql = "INSERT INTO Sessions VALUES (:username, :token, :last_used)";

    $epoch = $session->lastUsed->getTimestamp();
    $stmt  = $db->prepare($sql);
    $stmt->bindParam(':username', $session->username, PDO::PARAM_STR);
    $stmt->bindParam(':token', $session->token, PDO::PARAM_STR);
    $stmt->bindParam(':last_used', $epoch, PDO::PARAM_INT);

    return $stmt->execute();

}


function get_session(string $token, PDO $db) : ?Session
{
    $sql  = "SELECT * FROM Sessions WHERE token = :token";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row === false || empty($row) === true) {
        return null;
    }

    return new Session($row['user'], $row['token'], (int) $row['lastUsedDate']);

}


function remove_session(string $token, PDO $db) : bool
{
    $sql  = "DELETE FROM Sessions WHERE token = :token";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);

    setcookie('session', '', (time() - 3600), '/');

    return $stmt->execute();

}


function change_sessions_username(string $oldUsername, string $newUsername, PDO $db) : bool
{
    $sql  = "UPDATE Sessions SET user = :newUsername WHERE user = :oldUsername";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':newUsername', $newUsername, PDO::PARAM_STR);
    $stmt->bindParam(':oldUsername', $oldUsername, PDO::PARAM_STR);

    return $stmt->execute();

}
