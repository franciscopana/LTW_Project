<?php

require_once __DIR__.'/../database/database.php';
require_once __DIR__.'/../database/tickets.db.php';
require_once __DIR__.'/../database/labels.db.php';
require_once __DIR__.'/../utils/session.php';
require_once __DIR__.'/../utils/roles.php';
require_once __DIR__.'/../utils/routing.php';

$db = get_database();
header("Content-Type: application/json");

handle_api_route(
    "/tickets",
    "GET",
    function () use ($db) {
        if (is_session_valid($db) === null) {
            http_response_code(403);

            echo '{"error":"user not authenticated"}';
            exit();
        }

        $session = is_session_valid($db);
        $client  = get_user($session->username, $db);

        $limit  = min(intval(($_GET["limit"] ?? 4)), 20);
        $offset = intval(($_GET["offset"] ?? 0));

        $sortOrder = ($_GET["sortOrder"] ?? 'DESC');
        if ($sortOrder !== 'ASC' && $sortOrder !== 'DESC') {
            $sortOrder = 'DESC';
        }

        $text = ($_GET["text"] ?? '');
        $text = "%$text%";

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

        echo json_encode(array_values($tickets));
    }
);

handle_api_route(
    "/tickets/<id>",
    "GET",
    function (string $id) use ($db) {
        if (is_session_valid($db) === null) {
            http_response_code(403);

            echo '{"error":"user not authenticated"}';
            exit();
        }

        $ticket = get_ticket(intval($id), $db);
        echo json_encode($ticket);
    }
);


handle_api_route(
    "/tickets/<id>/labels",
    "POST",
    function (string $id) use ($db) {
        if (is_session_valid($db) === null) {
            http_response_code(403);

            echo '{"error":"user not authenticated"}';
            exit();
        }

        if (is_current_user_agent($db) === false) {
            http_response_code(403);

            echo '{"error":"user without permissions"}';
            exit();
        }

        $ticket = get_ticket(intval($id), $db);

        if ($ticket === null) {
            http_response_code(404);
            echo '{"error":"ticket not found"}';
            exit();
        }

        $body = json_decode(file_get_contents("php://input"), true);
        if ($body === null) {
            http_response_code(400);
            exit();
        }

        $label = get_label($body["label"], $db);
        if ($label === null) {
            http_response_code(404);
            echo '{"error":"label not found"}';
            exit();
        }

        add_label_to_ticket($ticket, $label, $db);
    }
);


handle_api_route(
    "/tickets/<id>/labels",
    "DELETE",
    function (string $id) use ($db) {
        if (is_session_valid($db) === null) {
            http_response_code(403);

            echo '{"error":"user not authenticated"}';
            exit();
        }

        if (is_current_user_agent($db) === false) {
            http_response_code(403);

            echo '{"error":"user without permissions"}';
            exit();
        }

        $ticket = get_ticket(intval($id), $db);

        if ($ticket === null) {
            http_response_code(404);
            echo '{"error":"ticket not found"}';
            exit();
        }

        $body = json_decode(file_get_contents("php://input"), true);
        if ($body === null) {
            http_response_code(400);
            exit();
        }

        $label = get_label($body["label"], $db);
        if ($label === null) {
            http_response_code(404);
            echo '{"error":"label not found"}';
            exit();
        }

        remove_label_from_ticket($ticket, $label, $db);
    }
);

no_api_route();
