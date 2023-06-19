<?php

declare(strict_types=1);

require_once __DIR__."/status.db.php";
require_once __DIR__."/labels.db.php";
require_once __DIR__.'/../database/faq.db.php';
require_once "utils/datetime.php";

class Ticket implements JsonSerializable
{

    public int $id;

    public string $title;

    public string $description;

    public Status $status;

    public readonly array $labels;

    public ?Client $assignee;

    public string $createdByUser;

    public string $department;

    public Datetime $createdAt;

    public FAQ $faq;


    public function jsonSerialize() : mixed
    {
        return [
            "id"            => $this->id,
            "title"         => $this->title,
            "description"   => $this->description,
            "status"        => $this->status,
            "labels"        => $this->labels,
            "assignee"      => $this->assignee,
            "createdByUser" => $this->createdByUser,
            "createdAt"     => $this->createdAt,
            "timeAgo"       => time_ago($this->createdAt),
            "department"    => $this->department,
        ];

    }


    public function __construct(string $title, string $description, int $_epoch, Status $status,
        array $labels, int $id=0, ?Client $assignee=null, string $createdByUser="", string $department="", FAQ $faq=new FAQ(0)
    ) {
        $this->id            = $id;
        $this->title         = $title;
        $this->description   = $description;
        $this->status        = $status;
        $this->labels        = $labels;
        $this->assignee      = $assignee;
        $this->createdByUser = $createdByUser;
        $this->department    = $department;
        $this->createdAt     = new DateTime("@".$_epoch);
        $this->faq           = $faq;

    }


}


function create_ticket_object(array $ticket, PDO $db) : Ticket
{
    $sql  = "SELECT * FROM Status WHERE status=:status";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":status", $ticket["status"]);
    $stmt->execute();

    $result = $stmt->fetch();

    $status = new Status(
        $result["status"],
        $result["color"],
        $result["backgroundColor"],
        $result["createdAt"]
    );

    $sql  = "SELECT * FROM LabelTicket l JOIN Labels h ON h.label=l.label WHERE ticket=:id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":id", $ticket["id"], PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll();

    $labels = array_map(
        function (array $a): Label {
            return new Label(
                $a["label"],
                $a["color"],
                $a["backgroundColor"],
                $a["createdAt"]
            );
        },
        $result
    );

    $assignee = null;
    if (isset($ticket["assignee"]) === true) {
        $assignee = get_user($ticket["assignee"], $db);
    }

    return new Ticket(
        $ticket["title"],
        $ticket["description"],
        $ticket["createdAt"],
        $status,
        $labels,
        $ticket["id"],
        $assignee,
        $ticket["createdByUser"],
        ($ticket["department"] ?? ''),
        (get_faq((int) $ticket['faq'], $db) ?? new FAQ(0))
    );

};


function getUnassignedTickets(PDO $db, $limit, $offset, $sortOrder, $text): array
{
    $text = "%$text%";

    $sql  = "SELECT * FROM Tickets WHERE assignee IS NULL AND (status != 'closed' OR status IS NULL) AND (id LIKE :text OR title LIKE :text OR description LIKE :text) ORDER BY createdAt $sortOrder LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":text", $text, PDO::PARAM_STR);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);

    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tickets = [];

    foreach ($result as $ticket) {
        $tickets[] = create_ticket_object($ticket, $db);
    }

    return $tickets;

}


function getTicketsAssignedTo(string $username, PDO $db, $limit, $offset, $sortOrder, $text): array
{
    $text = "%$text%";
    $sql  = "SELECT * FROM tickets WHERE assignee = :username AND status != 'closed' AND (id LIKE :text OR title LIKE :text OR description LIKE :text) ORDER BY createdAt $sortOrder LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":text", $text, PDO::PARAM_STR);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tickets = [];

    foreach ($result as $ticket) {
        $tickets[] = create_ticket_object($ticket, $db);
    }

    return $tickets;

}


function getTicketsCreatedBy(string $username, PDO $db, $limit, $offset, $sortOrder, $text): array
{
    $text = "%$text%";
    $sql  = "SELECT * FROM Tickets WHERE createdByUser = :username AND status != 'closed' AND (id LIKE :text OR title LIKE :text OR description LIKE :text) ORDER BY createdAt $sortOrder LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":text", $text, PDO::PARAM_STR);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tickets = [];

    foreach ($result as $ticket) {
        $tickets[] = create_ticket_object($ticket, $db);
    }

    return $tickets;

}


function getAllTickets(PDO $db, $limit, $offset, $sortOrder, $text): array
{
    $text = "%$text%";
    $sql  = "SELECT * FROM tickets WHERE (id LIKE :text OR title LIKE :text OR description LIKE :text) ORDER BY createdAt $sortOrder LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":text", $text, PDO::PARAM_STR);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tickets = [];

    foreach ($result as $ticket) {
        $tickets[] = create_ticket_object($ticket, $db);
    }

    return $tickets;

}


function getArchivedTickets(PDO $db, $limit, $offset, $sortOrder, $text): array
{
    $text = "%$text%";
    $sql  = "SELECT * FROM tickets WHERE status = 'closed' AND (id LIKE :text OR title LIKE :text OR description LIKE :text) ORDER BY createdAt $sortOrder LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":text", $text, PDO::PARAM_STR);
    $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
    $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tickets = [];

    foreach ($result as $ticket) {
        $tickets[] = create_ticket_object($ticket, $db);
    }

    return $tickets;

}


function insert_new_ticket(Session $session, Ticket $ticket, PDO $db) : int
{
    $sql = "INSERT INTO Tickets(title, description, createdByUser, createdAt, status) VALUES (:title, :description, :createByUser, :createdAt, 'open')";

    $time = $ticket->createdAt->getTimestamp();
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':createByUser', $session->username, PDO::PARAM_STR);
    $stmt->bindParam(':title', $ticket->title, PDO::PARAM_STR);
    $stmt->bindParam(':description', $ticket->description, PDO::PARAM_STR);
    $stmt->bindParam(':createdAt', $time, PDO::PARAM_INT);

    if ($stmt->execute() === false) {
        return 0;
    }

    return (int) $db->lastInsertId();

}


function get_ticket(int $id, PDO $db) : ?Ticket
{
    $sql  = "SELECT * FROM Tickets WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();
    if ($result === false) {
        return null;
    }

    return create_ticket_object($result, $db);

}


function update_ticket_department(Ticket $ticket, PDO $db) : bool
{
    if ($ticket->department === "") {
        $sql = "UPDATE Tickets SET department = NULL WHERE id = :id";
    } else {
        $sql = "UPDATE Tickets SET department = :department WHERE id = :id";
    }

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $ticket->id, PDO::PARAM_INT);

    if ($ticket->department !== "") {
        $stmt->bindParam(':department', $ticket->department, PDO::PARAM_STR);
    }

    return $stmt->execute();

}


function update_ticket_status(Ticket $ticket, PDO $db) : bool
{
    if ($ticket->faq->id !== 0) {
        $sql  = "UPDATE Tickets SET status = :status, faq = :faq WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $ticket->id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $ticket->status->status, PDO::PARAM_STR);
        $stmt->bindParam(':faq', $ticket->faq->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    $sql  = "UPDATE Tickets SET status = :status WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $ticket->id, PDO::PARAM_INT);
    $stmt->bindParam(':status', $ticket->status->status, PDO::PARAM_STR);
    return $stmt->execute();

}


function update_ticket_assignee(Ticket $ticket, PDO $db) : bool
{
    if ($ticket->assignee->username === "") {
        $sql  = "UPDATE Tickets SET assignee = NULL WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $ticket->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    $sql  = "UPDATE Tickets SET assignee = :assignee WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $ticket->id, PDO::PARAM_INT);
    $stmt->bindParam(':assignee', $ticket->assignee->username, PDO::PARAM_STR);
    return $stmt->execute();

}


function add_label_to_ticket(Ticket $ticket, Label $label, PDO $db) : bool
{
    $sql  = "INSERT INTO LabelTicket VALUES (:label, :ticket);";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':ticket', $ticket->id, PDO::PARAM_INT);
    $stmt->bindParam(':label', $label->label);

    return $stmt->execute();

}


function remove_label_from_ticket(Ticket $ticket, Label $label, PDO $db) : bool
{
    $sql  = "DELETE FROM LabelTicket WHERE ticket=:ticket AND label=:label;";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':ticket', $ticket->id, PDO::PARAM_INT);
    $stmt->bindParam(':label', $label->label);

    return $stmt->execute();

}
