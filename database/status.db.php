<?php

declare(strict_types=1);

class Status
{

    public string $status;

    public string $color;

    public string $backgroundColor;

    public int $createdAt;


    public function __construct(string $_status, string $_color, string $_backgroundColor,
        int $_createdAt
    ) {
        $this->status          = $_status;
        $this->color           = $_color;
        $this->backgroundColor = $_backgroundColor;
        $this->createdAt       = $_createdAt;

    }


}


function get_all_status(PDO $db) : array
{
    $stmt = $db->query("SELECT * FROM Status");

    $results = $stmt->fetchAll();

    return array_map(
        function (array $a) {
            return new Status($a["status"], $a["color"], $a["backgroundColor"], $a["createdAt"]);
        },
        $results
    );

}


function get_status(string $name, PDO $db) : ?Status
{
    $sql  = "SELECT * FROM Status WHERE status=:status";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":status", $name);
    $stmt->execute();

    $a = $stmt->fetch();

    if ($a === false) {
        log_to_stdout("Something went wrong while getting $name", "e");
        return null;
    }

    return new Status($a["status"], $a["color"], $a["backgroundColor"], $a["createdAt"]);

}


function add_status(string $status, string $color, string $backgroundColor, PDO $db) : bool
{
    $sql = "INSERT INTO Status VALUES (:status, :color, :backgroundColor, :createdTime)";

    $time = time();
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":status", $status);
    $stmt->bindParam(":color", $color);
    $stmt->bindParam(":backgroundColor", $backgroundColor);
    $stmt->bindParam(":createdTime", $time, PDO::PARAM_INT);

    return $stmt->execute();

}


function delete_status(string $status, PDO $db) : bool
{
    $sql  = "DELETE FROM Status WHERE status=:status";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":status", $status);

    return $stmt->execute();

}


function edit_status(string $status, string $color, string $backgroundColor, PDO $db) : bool
{
    $sql = "UPDATE Status SET color=:color, backgroundColor=:backgroundColor WHERE status=:status";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(":status", $status);
    $stmt->bindParam(":color", $color);
    $stmt->bindParam(":backgroundColor", $backgroundColor);

    return $stmt->execute();

}
