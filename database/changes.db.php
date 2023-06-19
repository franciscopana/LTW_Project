<?php

declare(strict_types=1);

class Change
{

    public int $id;

    public DateTime $timestamp;

    public int $ticketId;

    public Client $user;


    public function __construct(int $id, int $ticketId, int $epoch, Client $user)
    {
        $this->id        = $id;
        $this->timestamp = new DateTime("@".$epoch);
        $this->user      = $user;
        $this->ticketId  = $ticketId;

    }


}

class AssignedChange extends Change
{

    public Client $agent;


    public function __construct(int $id, int $ticketId, int $epoch, Client $user, Client $agent)
    {
        parent::__construct($id, $ticketId, $epoch, $user);
        $this->agent = $agent;

    }


}

class StatusChange extends Change
{

    public string $status;


    public function __construct(int $id, int $ticketId, int $epoch, Client $user, string $status)
    {
        parent::__construct($id, $ticketId, $epoch, $user);
        $this->status = $status;

    }


}


function insert_new_change($change, PDO $db) : bool
{
    if ($change instanceof AssignedChange === false and $change instanceof StatusChange === false) {
        return false;
    }

    $sql       = "INSERT INTO Changes (timestamp, user) VALUES (:timestamp, :user)";
    $stmt      = $db->prepare($sql);
    $timestamp = $change->timestamp->getTimestamp();
    $stmt->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
    $stmt->bindParam(':user', $change->user->username, PDO::PARAM_STR);
    $stmt->execute();

    $changeId = $db->lastInsertId();

    $sql  = "INSERT INTO TicketsChanges (ticket, change) VALUES (:ticket, :change)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':ticket', $change->ticketId, PDO::PARAM_INT);
    $stmt->bindParam(':change', $changeId, PDO::PARAM_INT);
    $stmt->execute();

    if ($change instanceof AssignedChange === true) {
        if ($change->agent->username === "") {
            $sql  = "INSERT INTO AssignedChanges (change, agent) VALUES (:change, NULL)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':change', $changeId, PDO::PARAM_INT);
            return $stmt->execute();
        }

        $sql  = "INSERT INTO AssignedChanges (change, agent) VALUES (:change, :agent)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':change', $changeId, PDO::PARAM_INT);
        $stmt->bindParam(':agent', $change->agent->username, PDO::PARAM_STR);
        return $stmt->execute();
    } else {
        $sql  = "INSERT INTO StatusChanges (change, status) VALUES (:change, :status)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':change', $changeId, PDO::PARAM_INT);
        $stmt->bindParam(':status', $change->status, PDO::PARAM_STR);
        return $stmt->execute();
    }

}


function get_changes_by_ticket(int $ticketId, PDO $db) : array
{
    // I have  StatusChanges, Changes and TicketsChanges tables
    $sql  = "SELECT * FROM StatusChanges JOIN Changes ON StatusChanges.change = Changes.id JOIN TicketsChanges ON Changes.id = TicketsChanges.change WHERE ticket = :ticketId";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
    $stmt->execute();

    $changes = [];
    while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
        $change = new StatusChange(
            (int) $row['id'],
            $row['ticket'],
            $row['timestamp'],
            get_user($row['user'], $db),
            $row['status']
        );
        array_push($changes, $change);
    }

    $sql  = "SELECT * FROM AssignedChanges JOIN Changes ON AssignedChanges.change = Changes.id JOIN TicketsChanges ON Changes.id = TicketsChanges.change WHERE ticket = :ticketId";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
    $stmt->execute();

    while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
        $change = new AssignedChange(
            (int) $row['id'],
            $row['ticket'],
            $row['timestamp'],
            get_user($row['user'], $db),
            (get_user(($row['agent'] ?? ""), $db) ?? new Client("", ""))
        );
            array_push($changes, $change);
    }

    return $changes;

}
