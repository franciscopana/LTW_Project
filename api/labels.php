<?php
require_once __DIR__.'/../database/database.php';
require_once __DIR__.'/../database/client.db.php';
require_once __DIR__.'/../database/labels.db.php';
require_once __DIR__.'/../utils/session.php';
require_once __DIR__.'/../utils/routing.php';

$db = get_database();
header("Content-Type: application/json");

handle_api_route(
    "/labels",
    "GET",
    function () use ($db) {
        $query = ($_GET["q"] ?? '');

        $labels = get_all_labels($db);

        $labels = array_filter(
            $labels,
            function (Label $label) use ($query) : bool {
                return str_contains(strtolower($label->label), strtolower($query));
            }
        );

        echo json_encode($labels);
    }
);


no_api_route();
