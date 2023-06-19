<?php
require_once 'components/navbar/navbar.php';
require_once 'components/ticket-card/ticket-card.php';
require_once 'database/database.php';
require_once 'database/tickets.db.php';
require_once 'components/layout/layout.php';

$db = get_database();

$session = is_session_valid($db);
if ($session === null) {
    header("Location: /login");
    exit();
}

$client = null;

if ($session !== null) {
    $client = get_user($session->username, $db);
}

$limit  = min(intval(($_GET["limit"] ?? 4)), 20);
$offset = intval(($_GET["offset"] ?? 0));

$sortOrder = ($_GET["sortOrder"] ?? 'DESC');
if ($sortOrder !== 'ASC' && $sortOrder !== 'DESC') {
    $sortOrder = 'DESC';
}

$text = ($_GET["text"] ?? '');

$tab = ($_GET["tab"] ?? "unassigned");
if ($tab === "unassigned" || $tab === null) {
    $tickets = getUnassignedTickets($db, $limit, $offset, $sortOrder, $text);
} else if ($tab === "assignedToMe") {
    $tickets = getTicketsAssignedTo($client->username, $db, $limit, $offset, $sortOrder, $text);
} else if ($tab === "createdByMe") {
    $tickets = getTicketsCreatedBy($client->username, $db, $limit, $offset, $sortOrder, $text);
} else if ($tab === "allTickets") {
    $tickets = getAllTickets($db, $limit, $offset, $sortOrder, $text);
} else if ($tab === "archived") {
    $tickets = getArchivedTickets($db, $limit, $offset, $sortOrder, $text);
}

layout_start();

?>
<link rel="stylesheet" href="/css/tickets.css">
<link rel="stylesheet" href="/components/ticket-card/ticket-card.css">
<script src="/js/tickets.js"></script>

<h1>Tickets</h1>
<ul id="buttons">
    <li>
        <input type="text" id="search" placeholder="Search" value="<?php echo htmlspecialchars(htmlspecialchars($text))?>">
    </li>
    <li>
        <select id="sortSelect" onchange="handleSortOptionChange(this.value)">
            <option value="lastCreated" <?php
            if ($sortOrder === 'DESC') {
                echo 'selected';
            } ?>>Last Created</option>
            <option value="firstCreated" <?php
            if ($sortOrder === 'ASC') {
                echo 'selected';
            } ?>>First Created</option>
        </select>
    </li>
    <li>
        <a href="/newTicket" class="button primary">New ticket</a>
    </li>
</ul>

<ul class="tabSelector" id="filters">
    <li <?php
    if ($tab === "unassigned" || $tab === null) {
        echo 'class="active"';
    } ?>>
        <a href="?tab=unassigned&text=<?php echo htmlspecialchars(urlencode($text))?>">Unassigned</a>
    </li>
    <li <?php
    if ($tab === "assignedToMe") {
        echo 'class="active"';
    } ?>>
        <a href="?tab=assignedToMe&text=<?php echo htmlspecialchars(urlencode($text))?>">Assigned to me</a>
    </li>
    <li <?php
    if ($tab === "createdByMe") {
        echo 'class="active"';
    } ?>>
        <a href="?tab=createdByMe&text=<?php echo htmlspecialchars(urlencode($text))?>">Created by me</a>
    </li>
    <li <?php
    if ($tab === "allTickets") {
        echo 'class="active"';
    } ?>>
        <a href="?tab=allTickets&text=<?php echo htmlspecialchars(urlencode($text))?>">All tickets</a>
    </li>
    <li <?php
    if ($tab === "archived") {
        echo 'class="active"';
    } ?>>
        <a href="?tab=archived&text=<?php echo htmlspecialchars(urlencode($text))?>">Archived</a>
    </li>
</ul>

<div class="ticket-list">
    <?php foreach ($tickets as $ticket) {
        ticketCard($ticket);
    } ?>
</div>
<?php
layout_end();
