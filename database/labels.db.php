<?php

declare(strict_types=1);

class Label
{

    public string $label;

    public string $color;

    public string $backgroundColor;

    public int $createdAt;


    public function __construct(string $_label, string $_color, string $_backgroundColor,
        int $_createdAt
    ) {
        $this->label           = $_label;
        $this->color           = $_color;
        $this->backgroundColor = $_backgroundColor;
        $this->createdAt       = $_createdAt;

    }


}


function get_all_labels(PDO $db) : array
{
    $stmt = $db->query("SELECT * FROM Labels");

    $results = $stmt->fetchAll();

    return array_map(
        function (array $a) {
            return new Label($a["label"], $a["color"], $a["backgroundColor"], $a["createdAt"]);
        },
        $results
    );

}


function add_label(string $label, string $color, string $backgroundColor, PDO $db) : bool
{
    $sql = "INSERT INTO Labels VALUES (:label, :color, :backgroundColor, :createdTime)";

    $time = time();
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":label", $label);
    $stmt->bindParam(":color", $color);
    $stmt->bindParam(":backgroundColor", $backgroundColor);
    $stmt->bindParam(":createdTime", $time, PDO::PARAM_INT);

    return $stmt->execute();

}


function edit_label(string $label, string $color, string $backgroundColor, PDO $db) : bool
{
    $sql = "UPDATE Labels SET color=:color, backgroundColor=:backgroundColor WHERE label=:label";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(":label", $label);
    $stmt->bindParam(":color", $color);
    $stmt->bindParam(":backgroundColor", $backgroundColor);

    return $stmt->execute();

}


function get_label(string $name, PDO $db) : ?Label
{
    $sql  = "SELECT * FROM Labels WHERE label=:label";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":label", $name);
    $stmt->execute();

    $a = $stmt->fetch();

    if ($a === false) {
        log_to_stdout("Something went wrong while getting $name", "e");
        return null;
    }

    return new Label($a["label"], $a["color"], $a["backgroundColor"], $a["createdAt"]);

}


function delete_label(string $label, PDO $db) : bool
{
    $sql  = "DELETE FROM Labels WHERE label=:label";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":label", $label);

    return $stmt->execute();

}
