<?php

require_once 'components/layout/layout.php';
require_once 'utils/session.php';
require_once 'utils/roles.php';
require_once 'utils/logger.php';
require_once 'database/database.php';

$db = get_database();

$session = is_session_valid($db);

if ($session === null) {
    header("Location /login");
    exit();
}

if (is_current_user_agent($db) === false) {
    header("Location /");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    //TODO: csrf
    if (isset($_POST["title"]) === false || isset($_POST["content"]) === false) {
        header("Location: /faq/new", false, 400);
        exit();
    }

    if (create_faq_entry($_POST["title"], $_POST["content"], $session->username, $db) === false) {
        log_to_stdout("Error while creating new faq entry...", "e");
    }

    header("Location: /faq");
    exit();
}

layout_start();


//TODO csrf
?>
<link rel="stylesheet" type="text/css" href="/css/faq-new.css">
<h1>Create a FAQ</h1>
<hr>

<form method="post" class="create-faq">
    <label for="title">
        <p>Question:</p>
    </label>
    <input type="text" name="title" required placeholder="What is the question?">

    <label for="content">
        <p>Answer:</p>
    </label>
    <textarea class="content-area" name="content" rows="10" required placeholder="Write the corresponding answer"></textarea>

    <input type="submit" class="primary" value="Create">

</form>

<?php

layout_end();
