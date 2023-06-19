<?php
declare(strict_types=1);

require_once __DIR__.'/../database/tickets.db.php';
require_once __DIR__.'/../database/comments.db.php';
require_once __DIR__.'/../database/department.db.php';
require_once __DIR__.'/../database/changes.db.php';
require_once __DIR__.'/../database/faq.db.php';


function create_ticket(string $title, string $description, PDO $db) : bool
{

    $ticket  = new Ticket($title, $description, time(), get_status('open', $db), []);
    $session = is_session_valid($db);

    $ticketId = insert_new_ticket($session, $ticket, $db);

    if ($ticketId === 0) {
        return false;
    }

    header('Location: /ticket?id='.$ticketId);
    return true;

}


function create_comment(string $content, int $ticketId, PDO $db) : ?string
{

    if ($content === null || $ticketId === null) {
        return 'Invalid comment';
    }

    if ($content === '') {
        return 'Comment cannot be empty';
    }

    $ticket = get_ticket($ticketId, $db);
    if ($ticket === null) {
        return 'Ticket not found';
    }

    $session = is_session_valid($db);
    if ($session === null) {
        return 'Session not found';
    }

    $createdByUser = new Client($session->username);
    $createAt      = time();
    $comment       = new Comment($content, $ticketId, $createdByUser, $createAt);
    $commentId     = insert_new_comment($comment, $db);

    if ($commentId === false) {
        return 'Error inserting comment';
    }

    return null;

}


function get_comments(int $ticketId, PDO $db) : array
{

    return get_comments_by_ticket($ticketId, $db);

}


function get_changes(int $ticketId, PDO $db) : array
{

    return get_changes_by_ticket($ticketId, $db);

}


function get_ticket_author(string $username, PDO $db) : ?Client
{

    return get_user($username, $db);

}


function change_department(int $ticketId, string $department, PDO $db) : ?string
{

    $ticket = get_ticket($ticketId, $db);
    if ($ticket === null) {
        return 'Ticket not found';
    }

    // Removing department
    if ($department === '') {
        $ticket->department = '';
        if (update_ticket_department($ticket, $db) === false) {
            return 'Error updating ticket';
        }

        return null;
    }

    $department = get_department($department, $db);
    if ($department === null) {
        return 'Department not found';
    }

    $ticket->department = $department->name;

    if (update_ticket_department($ticket, $db) !== true) {
        return 'Error updating ticket';
    }

    return null;

}


function close_ticket(int $ticketId, ?int $faqId, PDO $db) : ?string
{
    $closed = get_status("closed", $db);

    $ticket = get_ticket($ticketId, $db);
    if ($ticket === null) {
        return 'Ticket not found';
    }

    if ($ticket->status->status === 'closed') {
        return 'Ticket is already closed';
    }

    if ($faqId !== null) {
        $faq = get_faq($faqId, $db);
        if ($faq === null) {
            return "FAQ not found";
        }

        $ticket->faq = $faq;
    }

    $ticket->status = $closed;

    if (update_ticket_status($ticket, $db) !== true) {
        return 'Error updating ticket';
    }

    // Creating a change
    $session  = is_session_valid($db);
    $changeBy = new Client($session->username, '', '');
    $changeAt = time();
    $change   = new StatusChange(0, $ticket->id, $changeAt, $changeBy, $ticket->status->status);
    if (insert_new_change($change, $db) === null) {
        return 'Error inserting the change';
    }

    return null;

}


function open_ticket(int $ticketId, PDO $db) : ?string
{
    $open     = get_status("open", $db);
    $assigned = get_status("assigned", $db);

    $ticket = get_ticket($ticketId, $db);
    if ($ticket === null) {
        return 'Ticket not found';
    }

    if ($ticket->status->status !== 'closed') {
        return 'Ticket is already open';
    }

    $ticket->status = $open;
    if ($ticket->assignee !== null) {
        $ticket->status = $assigned;
    }

    if (update_ticket_status($ticket, $db) !== true) {
        return 'Error updating ticket';
    }

    $ticket->status = $open;

    // Creating a change
    $session  = is_session_valid($db);
    $changeBy = new Client($session->username, '', '');
    $changeAt = time();
    $change   = new StatusChange(0, $ticket->id, $changeAt, $changeBy, $ticket->status->status);
    if (insert_new_change($change, $db) === null) {
        return 'Error inserting the change';
    }

    return null;

}


function change_status(int $ticketId, string $status, PDO $db) : ?string
{
    $ticket = get_ticket($ticketId, $db);
    if ($ticket === null) {
        return 'Ticket not found';
    }

    $status = get_status($status, $db);
    if ($status === null) {
        return 'Status not found';
    }

    $ticket->status = $status;

    if (update_ticket_status($ticket, $db) !== true) {
        return 'Error updating ticket';
    }

    $session  = is_session_valid($db);
    $changeBy = new Client($session->username, '', '');
    $changeAt = time();
    $change   = new StatusChange(0, $ticket->id, $changeAt, $changeBy, $ticket->status->status);
    if (insert_new_change($change, $db) === null) {
        return 'Error inserting the change';
    }

    return null;

}


function assign_ticket($ticketId, $user, $db)
{
    $assigned = get_status("assigned", $db);
    $closed   = get_status("closed", $db);

    $ticket = get_ticket($ticketId, $db);
    if ($ticket === null) {
        return 'Ticket not found';
    }

    $user = get_user($user, $db);
    if ($user === null) {
        return 'User not found';
    }

    $ticket->assignee = $user;

    if (update_ticket_assignee($ticket, $db) !== true) {
        return 'Error updating ticket';
    }

    if ($ticket->status->status !== 'closed') {
        $ticket->status = $assigned;
    }

    if (update_ticket_status($ticket, $db) !== true) {
        return 'Error updating ticket';
    }

    // Creating a change
    $session  = is_session_valid($db);
    $changeBy = new Client($session->username, '', '');
    $changeAt = time();
    $change   = new AssignedChange(0, $ticket->id, $changeAt, $changeBy, $ticket->assignee);
    if (insert_new_change($change, $db) === null) {
        return 'Error inserting the change';
    }

    return null;

}


function unassign_ticket($ticketId, $db)
{
    $open = get_status("open", $db);

    $ticket = get_ticket($ticketId, $db);
    if ($ticket === null) {
        return 'Ticket not found';
    }

    $ticket->assignee = new Client('');

    if (update_ticket_assignee($ticket, $db) !== true) {
        return 'Error updating ticket';
    }

    if ($ticket->status->status === 'assigned') {
        $ticket->status = $open;
    }

    if (update_ticket_status($ticket, $db) !== true) {
        return 'Error updating ticket';
    }

    // Creating a change
    $session  = is_session_valid($db);
    $changeBy = new Client($session->username, '', '');
    $changeAt = time();
    $change   = new AssignedChange(0, $ticket->id, $changeAt, $changeBy, $ticket->assignee);
    if (insert_new_change($change, $db) === null) {
        return 'Error inserting the change';
    }

    return null;

}
