<?php
declare(strict_types=1);

require_once __DIR__."/../utils/env_file_parser.php";
require_once __DIR__."/../utils/hash.php";


function get_database()
{
    $dbPath = "database/tickets.db";
    //this is a bit hacky but it will work for now
    if (str_contains(getcwd(), "api") === true) {
        $dbPath = "../database/tickets.db";
    }

    if (is_file($dbPath) === true) {
        $pdo = new PDO("sqlite:".$dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('pragma foreign_keys = ON');
        return $pdo;
    }

    $pdo = new PDO("sqlite:".$dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('pragma foreign_keys = ON');
    $schemaFile = fopen("database/create.sql", "r");
    $schema     = fread($schemaFile, filesize("database/create.sql"));

    $pdo->exec($schema);
    make_default_admin($pdo);

    return $pdo;

}


function make_default_admin(PDO $db)
{
    $env = parse_env_file(".env");
    if (array_key_exists("DEFAULT_ADMIN_USERNAME", $env) === false
        || array_key_exists("DEFAULT_ADMIN_PASSWORD", $env) === false
    ) {
        //TODO: throw lmao
        throw new ErrorException("Couldn't create default admin");
    }

    $username = $env["DEFAULT_ADMIN_USERNAME"];
    $password = $env["DEFAULT_ADMIN_PASSWORD"];
    $email    = $env["DEFAULT_ADMIN_EMAIL"];
    $name     = "Admin";
    $image    = "assets/images/default_user.png";
    $sql      = "INSERT INTO Clients VALUES (:user, :email, :password, :name, :image, :createdAt, 1);";
    $stmt     = $db->prepare($sql);
    $stmt->bindParam(":user", $username);
    $password = hash_password($password);
    $stmt->bindParam(":password", $password);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":image", $image);
    $time = time();
    $stmt->bindParam(":createdAt", $time, PDO::PARAM_INT);

    if ($stmt->execute() === false) {
        throw new ErrorException("Query failed while creating default admin");
    }

    $sql2  = "INSERT INTO Agents VALUES (:username)";
    $stmt2 = $db->prepare($sql2);
    $stmt2->bindParam(":username", $username);
    if ($stmt2->execute() === false) {
        throw new ErrorException("Query failed while creating default admin");
    }

    $sql2  = "INSERT INTO Admins VALUES (:username)";
    $stmt2 = $db->prepare($sql2);
    $stmt2->bindParam(":username", $username);

    if ($stmt2->execute() === false) {
        throw new ErrorException("Query failed while creating default admin");
    }

}
