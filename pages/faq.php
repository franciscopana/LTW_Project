<?php

require_once 'components/layout/layout.php';
require_once 'utils/routing.php';
require_once 'utils/roles.php';
require_once 'utils/session.php';
require_once 'utils/logger.php';


require_once 'database/database.php';
require_once 'database/faq.db.php';



$db = get_database();

if (is_session_valid($db) === null) {
    header("Location: /login");
    exit();
}

handle_page_route("/faq/new", __DIR__."/faq/new.php");
handle_page_route("/faq/<id>", __DIR__."/faq/edit.php");


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (is_current_user_agent($db) === false) {
        header("Location: /faq");
        exit();
    }

    log_to_stdout(var_export($_POST, true));

    if (isset($_POST["action"]) === false) {
        header("Location: /");
        exit();
    }

    if ($_POST["action"] === "deleteFAQ" && isset($_POST["id"]) === true) {
        if (delete_faq(intval($_POST["id"]), $db) === false) {
            log_to_stdout("Something went wrong while deleting FAQ with id".$_POST["id"], "e");
        }
    }

    if (isset($_POST["lastHref"]) === true) {
        header("Location: ".$_POST["lastHref"]);
    } else {
        header("Location: /faq");
    }
}

layout_start();

$limit  = min(intval(($_GET["limit"] ?? 10)), 20);
$offset = intval(($_GET["offset"] ?? 0));

$faqs = get_FAQs($limit, $offset, $db);

?>
    <link rel="stylesheet" type="text/css" href="/css/faq.css">
    <link rel="stylesheet" type="text/css" href="/css/modal.css">

    <script src="/js/modal.js"></script>
    <script src="/js/faq.js"></script>
    

    <h1>FAQ</h1>
    <p>Frequently Asked Questions</p>
    <div class="top-content">
        <?php if (is_current_user_agent($db) === true) :?>
            <button  type="button" class="primary" onclick="location.href= '/faq/new'">Create new FAQ</button>
        <?php endif;?>
        <div class="search">
            <h4>Have Questions? We're here to help.</h4>
            <input type="text" placeholder="Search for the question" id="fq-search" autocomplete="off">
        </div>
    </div>
    <div class="faq-content">
        <?php foreach ($faqs as $faq) :
            $user    = get_user($faq->createdByUser, $db);
            $content = explode("\n", $faq->content)?>
        <div class="faq-question">
            <header>
                <h2>#<?php echo htmlspecialchars($faq->id)?> - <?php echo htmlspecialchars($faq->title)?></h2>
                <div class="faq-buttons">
                    <?php if (is_current_user_agent($db) === true) :?>
                        <i class="ri-edit-line" onclick="location.href = '/faq/<?php echo htmlspecialchars($faq->id)?>'"></i>
                    <?php endif;?>
                    <?php if (is_current_user_agent($db) === true) :?>
                        <i class="ri-delete-bin-line" onclick="makeDeleteModal(<?php echo htmlspecialchars($faq->id)?>)"></i>
                    <?php endif;?>
                    <i class="ri-add-circle-line open-close"></i>
                </div>
            </header>
            <div class="content">
                <?php foreach ($content as $paragraph) :?>
                <p><?php echo htmlspecialchars($paragraph)?></p>
                <?php endforeach;?>
                <div class="created-by">
                    <p>By:</p>
                    <img class="avatar" src="<?php echo htmlspecialchars($user->image)?>" alt="user">
                    <p class="display-name"><?php echo htmlspecialchars($user->displayName)?></p>
                </div>
            </div>
        </div>
        <?php endforeach;?>
    </div>

    <div class="faq-footer">
        <h2>Still have questions?</h2>
        <p>Don't worry. Create a ticket and we'll get back to you as soon as possible.</p>
        <a href="/newTicket" class="button">Create a ticket</a>
    </div>


<?php


layout_end();
