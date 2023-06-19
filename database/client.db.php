<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\MySource\Sniffs\PHP\AjaxNullComparisonSniff;

require_once __DIR__."/../utils/hash.php";
require_once __DIR__."/../utils/roles.php";
require_once __DIR__."/../utils/logger.php";


class Client implements JsonSerializable
{

    const DEFAULT_IMAGE = 'assets/images/default_user.png';

    public string $username;

    public ?string $displayName;

    public ?string $email;

    public ?string $password;

    public ?string $image;

    public ?string $type;

    public ?int $createdAt;


    public function jsonSerialize() : mixed
    {
        return [
            "username"    => $this->username,
            "email"       => $this->email,
            "displayName" => $this->displayName,
            "type"        => $this->type,
            "createdAt"   => $this->createdAt,
            "image"       => $this->image,

        ];

    }


    public function __construct(string $_username, ?string $_email=null,
        ?string $_password=null, ?string $_displayName=null, ?string $_image=null, ?int $_createdAt=null
    ) {
        $this->username    = $_username;
        $this->displayName = $_displayName;
        $this->email       = $_email;
        $this->password    = $_password;
        $this->image       = ($_image ?? self::DEFAULT_IMAGE);
        $this->displayName = $_displayName;
        if ($_createdAt === null) {
            $this->createdAt = time();
        } else {
            $this->createdAt = $_createdAt;
        }

    }


}


function insert_new_client(Client $client, PDO $db) : bool
{

    $sql = "INSERT INTO Clients VALUES (:username, :email, :password, :display_name, :image, :createdAt, 0)";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':username', $client->username, PDO::PARAM_STR);
    $stmt->bindParam(':email', $client->email, PDO::PARAM_STR);
    $stmt->bindParam(':password', $client->password, PDO::PARAM_STR);
    $stmt->bindParam(':display_name', $client->displayName, PDO::PARAM_STR);
    $stmt->bindParam(':image', $client->image, PDO::PARAM_STR);
    $stmt->bindParam(":createdAt", $client->createdAt, PDO::PARAM_INT);

    return $stmt->execute();

}


function user_already_exists(string $username, PDO $db) : bool
{
    $sql  = "SELECT * FROM Clients WHERE username = :username";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetch() !== false;

}


function get_user(?string $username, PDO $db) : ?Client
{
    if ($username === null || $username === "null") {
        $client       = new Client("null", "", "", "Deleted user");
        $client->type = "client";
        return $client;
    }

    $sql  = "SELECT * FROM Clients WHERE username = :username";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row === false) {
        return null;
    }

    $client = new Client($row['username'], $row['email'], $row['password'], $row['displayName'], $row['image'], $row["createdAt"]);

    $client->type = "client";

    $sql  = "SELECT COUNT(*) FROM Agents WHERE username = :username";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    $isAgent = $stmt->fetchColumn() === 1;

    if ($isAgent === true) {
        $client->type = "agent";
        $sql          = "SELECT COUNT(*) FROM Admins WHERE username = :username";
        $stmt         = $db->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $isAdmin = $stmt->fetchColumn() === 1;
        if ($isAdmin === true) {
            $client->type = "admin";
        }
    }

    return $client;

}


function edit_user(Client $newUser, PDO $db) : bool
{
    $sql = "UPDATE Clients SET email = :new_email, password = :new_password, displayName = :new_display_name, image = :new_image WHERE username = :username";

    $stmt = $db->prepare($sql);

    $stmt->bindParam(':new_email', $newUser->email, PDO::PARAM_STR);
    $stmt->bindParam(':new_password', $newUser->password, PDO::PARAM_STR);
    $stmt->bindParam(':new_display_name', $newUser->displayName, PDO::PARAM_STR);
    $stmt->bindParam(':new_image', $newUser->image, PDO::PARAM_STR);
    $stmt->bindParam(':username', $newUser->username, PDO::PARAM_STR);

    return $stmt->execute();

}


function set_default_client_image(Client $client, PDO $db)
{
    $sql      = "UPDATE Clients SET image = :image WHERE username = :username";
    $stmt     = $db->prepare($sql);
    $newImage = Client::DEFAULT_IMAGE;
    $stmt->bindParam(':image', $newImage, PDO::PARAM_STR);
    $stmt->bindParam(':username', $client->username, PDO::PARAM_STR);
    $stmt->execute();

}


function change_password(string $username, string $newPassword, PDO $db) : bool
{
    $hashedPassword = hash_password($newPassword);
    $sql            = "UPDATE Clients SET password=:password, passwordInvalidated=0 WHERE username=:username";
    $stmt           = $db->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

    return $stmt->execute();

}


function is_user_password_invalidated(string $username, PDO $db) : bool
{
    $sql  = "SELECT passwordInvalidated FROM Clients WHERE username=:username";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);

    $stmt->execute();

    return $stmt->fetchColumn() === 1;

}


function get_clients(?int $limit, ?int $offset, PDO $db) : array
{
    $sql = "";
    if ($limit === null) {
        $sql = "SELECT * FROM Clients c";
    } else {
        $sql = "SELECT * FROM Clients c LIMIT :limit OFFSET :offset";
    }

    $stmt = $db->prepare($sql);

    if ($limit !== null) {
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    }

    $stmt->execute();

    $sqlAgent    = "SELECT * FROM Agents";
    $resultAgent = array_map(
        function (array $a) : string {
            return $a[0];
        },
        $db->query($sqlAgent)->fetchAll()
    );

    $sqlAdmin    = "SELECT * FROM Admins";
    $resultAdmin = array_map(
        function (array $a) : string {
            return $a[0];
        },
        $db->query($sqlAdmin)->fetchAll()
    );

    $result = $stmt->fetchAll();

    $clientBuilder = function (array $a) use ($resultAgent, $resultAdmin): ?Client {
        $client       = new Client($a["username"], $a["email"], $a["password"], $a["displayName"], $a["image"], $a["createdAt"]);
        $client->type = "client";
        if (in_array($client->username, $resultAgent) === true) {
            $client->type = "agent";
            if (in_array($client->username, $resultAdmin) === true) {
                $client->type = "admin";
            }
        }

        return $client;
    };

    return array_map($clientBuilder, $result);

}


function get_clients_only(?int $limit, ?int $offset, PDO $db) : array
{
    $sql = "";
    if ($limit === null) {
        $sql = "SELECT * FROM Clients c WHERE username NOT IN (SELECT * FROM Agents)";
    } else {
        $sql = "SELECT * FROM Clients c WHERE username NOT IN (SELECT * FROM Agents) LIMIT :limit OFFSET :offset";
    }

    $stmt = $db->prepare($sql);

    if ($limit !== null) {
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    }

    $stmt->execute();
    $result = $stmt->fetchAll();
    return array_map(
        function (array $a) : Client {
            $client       = new Client($a["username"], $a["email"], $a["password"], $a["displayName"], $a["image"], $a["createdAt"]);
            $client->type = "client";
            return $client;
        },
        $result
    );

}


function get_agents(?int $limit, ?int $offset, PDO $db) : array
{
    $sql = "";
    if ($limit === null) {
        $sql = "SELECT * FROM Clients c JOIN Agents a ON a.username = c.username";
    } else {
        $sql = "SELECT * FROM Clients c JOIN Agents a ON a.username = c.username LIMIT :limit OFFSET :offset";
    }

    $stmt = $db->prepare($sql);

    if ($limit !== null) {
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    }

    $stmt->execute();
    $result = $stmt->fetchAll();
    return array_map(
        function (array $a) : Client {
            $client       = new Client($a["username"], $a["email"], $a["password"], $a["displayName"], $a["image"], $a["createdAt"]);
            $client->type = "agent";
            return $client;
        },
        $result
    );

}


function get_admins(int $limit, int $offset, PDO $db) : array
{
    $sql = "";
    if ($limit === null) {
        $sql = "SELECT * FROM Clients c JOIN Admins a ON a.username = c.username";
    } else {
        $sql = "SELECT * FROM Clients c JOIN Admins a ON a.username = c.username LIMIT :limit OFFSET :offset";
    }

    $stmt = $db->prepare($sql);

    if ($limit !== null) {
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    }

    $stmt->execute();
    $result = $stmt->fetchAll();
    return array_map(
        function (array $a) : Client {
            $client       = new Client($a["username"], $a["email"], $a["password"], $a["displayName"], $a["image"], $a["createdAt"]);
            $client->type = "admin";
            return $client;
        },
        $result
    );

}


function delete_client(string $username, PDO $db) : bool
{
    $sql  = "DELETE FROM Clients WHERE username=:username";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":username", $username);

    $stmt->execute();

    return $stmt->rowCount() >= 1;

}


function change_user_type(Client $client, string $newType, PDO $db)
{
    if ($client->type === $newType) {
        return;
    }

    //TODO: do other table migrations

    //spaghetti code, but oh well crunching time
    if ($newType === "agent" && $client->type === "client") {
        promote_to_agent($client->username, $db);
    }

    if ($newType === "admin" && $client->type === "client") {
        promote_to_agent($client->username, $db);
        promote_to_admin($client->username, $db);
    }

    if ($newType === "admin" && $client->type === "agent") {
        promote_to_admin($client->username, $db);
    }

    if ($newType === "client" && $client->type === "admin") {
        demote_to_agent($client->username, $db);
        demote_to_client($client->username, $db);
    }

    if ($newType === "agent" && $client->type === "admin") {
        demote_to_agent($client->username, $db);
    }

    if ($newType === "client" && $client->type === "agent") {
        demote_to_client($client->username, $db);
    }

}


function update_user(string $username, string $displayName, string $password, string $email, string $type, PDO $db) : bool
{

    $client = get_user($username, $db);
    if ($client === null) {
        log_to_stdout("Failed to get user ".$username, "e");
        return false;
    }

    change_user_type($client, $type, $db);

    if (strlen($password) === 0) {
        $sql  = "UPDATE Clients SET displayName=:displayName, email=:email WHERE username=:username";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":displayName", $displayName);
        $stmt->bindParam(":email", $email);

        return $stmt->execute();
    }

    $hash = hash_password($password);
    $sql  = "UPDATE Clients SET displayName=:displayName, email=:email, password=:password, passwordInvalidated=1 WHERE username=:username";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":displayName", $displayName);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $hash);

    $updateSucessful = $stmt->execute() && $stmt->rowCount() === 1;

    if ($updateSucessful === false) {
        return false;
    }

    //remove sessions
    $sql  = "DELETE FROM Sessions WHERE user=:username";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":username", $username);
    return $stmt->execute();

}
