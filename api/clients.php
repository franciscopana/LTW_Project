<?php
require_once __DIR__.'/../database/database.php';
require_once __DIR__.'/../database/client.db.php';
require_once __DIR__.'/../utils/session.php';
require_once __DIR__.'/../utils/roles.php';
require_once __DIR__.'/../utils/routing.php';

$db = get_database();
header("Content-Type: application/json");

handle_api_route(
    "/clients",
    "GET",
    function () use ($db) {
        if (is_session_valid($db) === null) {
            http_response_code(403);
            echo '{"error":"user not authenticated"}';
            exit();
        }

        $limit = ($_GET["limit"] ?? null);
        if ($limit !== null) {
            $limit = min(intval($limit), 20);
        }

        $offset = intval(($_GET["offset"] ?? 0));

        $query = ($_GET["q"] ?? null);

        $clients = [];
        if (isset($_GET["sort"]) === false) {
            $clients = get_clients($limit, $offset, $db);
        } else if ($_GET["sort"] === "client") {
            $clients = get_clients_only($limit, $offset, $db);
        } else if ($_GET["sort"] === "agent") {
            $clients = get_agents($limit, $offset, $db);
        } else if ($_GET["sort"] === "admin") {
            $clients = get_admins($limit, $offset, $db);
        }

        if ($query !== null) {
            $clients = array_filter(
                $clients,
                function ($client) use ($query) {
                    return strpos($client->username, $query) !== false
                        || strpos($client->displayName, $query) !== false
                        || strpos($client->email, $query) !== false;
                }
            );
        }

            echo json_encode(array_values($clients));
    }
);


handle_api_route(
    "/clients/<id>",
    "GET",
    function (mixed $id) use ($db) {
        $session = is_session_valid($db);
        if ($session === null) {
            http_response_code(403);
            echo '{"error":"user not authenticated"}';
            exit();
        }

        $client = null;

        if ($id === "me") {
            $client = get_user($session->username, $db);
        } else {
            $client = get_user($id, $db);
        }

        if ($client === null) {
            http_response_code(404);
            echo '{"error":"username not found"}';
            exit();
        }

        echo json_encode($client);
    }
);

no_api_route();
