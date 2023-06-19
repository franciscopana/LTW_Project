<?php

require_once __DIR__.'/../utils/session.php';
require_once __DIR__.'/../utils/action_logout.php';
require_once __DIR__.'/../database/database.php';

$db = get_database();

if (is_session_valid($db) !== null) {
    logout($db);
}

header('Location: /');
