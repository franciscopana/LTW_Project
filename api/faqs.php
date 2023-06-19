<?php

require_once __DIR__.'/../database/database.php';
require_once __DIR__.'/../database/faq.db.php';
require_once __DIR__.'/../utils/session.php';
require_once __DIR__.'/../utils/roles.php';
require_once __DIR__.'/../utils/routing.php';

$db = get_database();
header("Content-Type: application/json");

handle_api_route(
    "/faqs",
    "GET",
    function () use ($db) {
        if (is_session_valid($db) === null) {
            http_response_code(403);
            echo '{"error":"user not authenticated"}';
            exit();
        }

        $limit  = min(intval(($_GET["limit"] ?? 10)), 20);
        $offset = intval(($_GET["offset"] ?? 0));

        $faqs = [];

        if (isset($_GET["q"]) === true) {
            $faqs = search_faq_title($limit, $offset, $_GET["q"], $db);
        } else {
            $faqs = get_FAQs($limit, $offset, $db);
        }

        echo json_encode(array_values($faqs));
    }
);

no_api_route();
