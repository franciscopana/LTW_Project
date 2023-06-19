<?php

declare(strict_types=1);

class FAQ
{

    public int $id;

    public ?string $createdByUser;

    public string $title;

    public string $content;


    public function __construct(int $_id, ?string $_createdByUser=null,
        ?string $_title="", ?string $_content=""
    ) {
        $this->id            = $_id;
        $this->createdByUser = $_createdByUser;
        $this->title         = $_title;
        $this->content       = $_content;

    }


}


function get_FAQs(int $limit, int $offset, PDO $db) : array
{
    $sql = "SELECT * FROM FAQs LIMIT :limit OFFSET :offset";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);

    $stmt->execute();

    $result = $stmt->fetchAll();

    return array_map(
        function (array $a) : FAQ {
            return new FAQ($a["id"], $a["createdByUser"], $a["title"], $a["content"]);
        },
        $result
    );

}


function create_faq_entry(string $title, string $content, string $user, PDO $db) : bool
{
    $sql = "INSERT INTO FAQs (createdByUser, title, content) VALUES (:user, :title, :content)";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(":user", $user);
    $stmt->bindParam(":title", $title);
    $stmt->bindParam(":content", $content);

    return $stmt->execute();

}


function search_faq_title(int $limit, int $offset, string $searchQuery, PDO $db) : array
{
    $sql = "SELECT * FROM FAQs WHERE title LIKE :searchquery LIMIT :limit OFFSET :offset";

    $search = "%".$searchQuery."%";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindParam(":searchquery", $search);

    $stmt->execute();

    $result = $stmt->fetchAll();

    return array_map(
        function (array $a) : FAQ {
            return new FAQ($a["id"], $a["createdByUser"], $a["title"], $a["content"]);
        },
        $result
    );

}


function delete_faq(int $id, PDO $db) : bool
{
    $sql  = "DELETE FROM FAQs WHERE id=:id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->rowCount() === 1;

}


function get_faq(int $id, PDO $db) : ?FAQ
{
    $sql  = "SELECT * FROM FAQs WHERE id=:id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);

    $stmt->execute();
    $a = $stmt->fetch();
    if ($a === false) {
        return null;
    }

    return new FAQ($a["id"], $a["createdByUser"], $a["title"], $a["content"]);

}


function modify_faq_entry(int $id, string $title, string $content, string $user, PDO $db) : bool
{
    $sql  = "UPDATE FAQs SET title=:title, content=:content, createdByUser=:user WHERE id=:id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->bindParam(":user", $user);
    $stmt->bindParam(":title", $title);
    $stmt->bindParam(":content", $content);

    $stmt->execute();
    return $stmt->rowCount() === 1;

}
