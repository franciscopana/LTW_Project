<?php
    require_once 'components/navbar/navbar.php';
    require_once 'database/database.php';
    require_once 'utils/action_ticket.php';
    require_once 'utils/datetime.php';

    $db = get_database();

    $error   = null;
    $success = null;

    $session = is_session_valid($db);

    $loggedUser = null;
if ($session !== null) {
    $loggedUser = get_user($session->username, $db);
}

    // Adding a new comment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (is_session_valid($db) === null) {
        header('Location: /login');
        exit();
    }

    $action = ($_POST["action"] ?? "");
    switch ($action) {
    case "comment":
        $content = $_POST['content'];
        $ticket  = $_POST['ticketId'];

        if ($content === null || $ticket === null) {
            $error = 'Invalid comment';
        }

        if ($content === '') {
            $error = 'Comment cannot be empty';
        }

        if ($error === null) {
            $created = create_comment($content, $ticket, $db);
            if ($created === false) {
                $error = 'Error creating comment';
            }
        }
        break;
    case "open":
        $ticketId = ($_POST["ticketId"] ?? "");
        if ($ticketId === "") {
            $error = "Invalid ticket";
            break;
        }

        $error = open_ticket($ticketId, $db);
        if ($error === null) {
            $success = "Ticket opened successfully";
        }
        break;

    case "close":
        $ticketId = ($_POST["ticketId"] ?? "");
        $faqId    = ($_POST["faqId"] ?? null);
        if ($faqId !== null) {
            $faqId = intval($faqId);
        }

        if ($ticketId === "") {
            $error = "Invalid ticket";
            break;
        }

        $ticket = get_ticket($ticketId, $db);
        if ($ticket !== null) {
            if ($ticket->status === "closed") {
                $error = "Ticket is already closed";
                break;
            } else {
                $error = close_ticket($ticketId, $faqId, $db);
                if ($error === null) {
                    $success = "Ticket closed successfully";
                }
            }
        } else {
            $error = "Ticket not found";
            break;
        }
        break;
    case "assign":
        $ticketId = ($_POST["ticketId"] ?? null);
        $ticketId = intval($ticketId);
        $user     = ($_POST["user"] ?? null);
        $error    = assign_ticket($ticketId, $user, $db);
        if ($error === null) {
            $success = "Ticket assigned successfully";
        }
        break;
    case "unassign":
        $ticketId = ($_POST["ticketId"] ?? null);
        $ticketId = intval($ticketId);
        $error    = unassign_ticket($ticketId, $db);
        if ($error === null) {
            $success = "Ticket unassigned";
        }
        break;
    case "changeDepartment":
        $ticketId   = ($_POST["ticketId"] ?? null);
        $ticketId   = intval($ticketId);
        $department = ($_POST["department"] ?? "");
        $error      = change_department($ticketId, $department, $db);
        if ($error === null) {
            if ($department === "") {
                $success = "Department unassigned";
            } else {
                $success = "Department $department assigned";
            }
        }
        break;
    case "changeStatus":
        $ticketId = ($_POST["ticketId"] ?? null);
        $ticketId = intval($ticketId);
        $status   = ($_POST["status"] ?? "");
        $error    = change_status($ticketId, $status, $db);
        if ($error === null) {
            $success = "Status changed to $status";
        }
        break;
    }
}

    $id = $_GET['id'];
if ($id === null) {
    header('Location: /tickets');
}

    $ticket = get_ticket($id, $db);
if ($ticket === null) {
    header('Location: /tickets');
}

    $author = get_ticket_author($ticket->createdByUser, $db);

    $comments = get_comments($id, $db);
    $changes  = get_changes($id, $db);
    $all      = array_merge($comments, $changes);
    usort(
        $all,
        function ($a, $b) {
            return ($a->timestamp->getTimestamp() - $b->timestamp->getTimestamp());
        }
    );

    $departments = get_departments(0, 30, $db, false);

    $allStatus = get_all_status($db);

    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?php echo htmlspecialchars($ticket->id)?></title>
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/remixicon.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/ticket.css">
    <link rel="stylesheet" href="css/modal.css">
    <script src="js/ticket-page.js"></script>
    <script src="js/modal.js"></script>
    <script src="js/snackbar.js"></script>
</head>
<body>
    <?php
        $db = get_database();
        echo navbar($db);

    //TODO: SAFE??
    if ($error !== null) {
        echo "<script>snackbar('$error', 'error')</script>";
    }

    if ($success !== null) {
        echo "<script>snackbar('$success', 'success')</script>";
    }
    ?>
    <input type="hidden" id="ticketId" value="<?php echo htmlspecialchars($ticket->id)?>">
    <main class="ticket-page">
        <h1><?php echo htmlspecialchars("$ticket->title #$ticket->id")?></h1>
        <ul id="buttons">
            <li class="status"> <!-- Status: Open -->
                <p>
                    <i class="ri-flag-line"></i>
                    Status:
                </p>
                <?php
                $icons  = [
                    "open"     => "ri-checkbox-blank-circle-line",
                    "closed"   => "ri-checkbox-circle-line",
                    "assigned" => "ri-donut-chart-line",
                ];
                $status = $ticket->status->status;
                ?>
                    
                <span style="background-color: <?php echo htmlspecialchars($ticket->status->backgroundColor)?>; color: <?php echo htmlspecialchars($ticket->status->color)?>;">
                    <?php if (isset($icons[$status]) === true) :?>
                        <i class="<?php echo htmlspecialchars($icons[$status])?>"></i>
                    <?php endif;?>
                    <?php echo htmlspecialchars(ucfirst($status))?>
                </span>
            </li>
            <?php if ($ticket->status->status === "closed") {?>
                <form method="post" action="ticket?id=<?php echo $ticket->id ?>">
                    <input type="hidden" name="action" value="open">
                    <input type="hidden" name="ticketId" value="<?php echo htmlspecialchars($ticket->id)?>">
                    <li><button type = "button" onClick="submitGrandFatherForm(event,this)"> 
                        <i class="ri-book-open-line"></i>
                        Reopen ticket 
                    </button></li>
                </form>
            <?php } else { ?>
                <li>
                    <form method="post" action="ticket?id=<?php echo htmlspecialchars($ticket->id)?>">
                        <input type="hidden" name="action" value="close">
                        <input type="hidden" name="ticketId" value="<?php echo htmlspecialchars($ticket->id)?>">
                        <button type = "button" onClick="submitFatherForm(event,this)"> 
                            <i class="ri-archive-line"></i>
                            Close ticket 
                        </button>
                    </form>

                    <div class="change-status-options">
                        <header>
                            <i class="ri-arrow-down-s-line header"></i>
                        </header>
                        <ul class="content">
                            <li>
                                <button onclick="makeFaqModal(
                                    <?php
                                        echo "'$loggedUser->type'";
                                    ?>
                                )">Close with FAQ</button>
                            </li>
                            <?php foreach ($allStatus as $status) : ?>
                                <li>
                                    <form method="post" action="ticket?id=<?php echo $ticket->id ?>">
                                        <input type="hidden" name="action" value="changeStatus">
                                        <input type="hidden" name="ticketId" value="<?php echo $ticket->id ?>">
                                        <input type="hidden" name="status" value="<?php echo $status->status ?>">
                                        <button type = "button" onClick="submitFatherForm(event,this)"> 
                                            <?php echo ucfirst($status->status) ?>
                                        </button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </li>
            <?php } ?>
        </ul>

        <div>
            <!-- Ticket description -->
            <div class="ticket-comment">
                <div class="user-comment">
                    <div class="user">
                        <img class="avatar" src="<?php echo htmlspecialchars($author->image)?>" alt="user">
                        <h3>
                            <?php echo htmlspecialchars($author->displayName)?>
                        </h3>
                        <p>
                            <?php echo htmlspecialchars(time_ago($ticket->createdAt))?>
                        </p>
                    </div>
                    <p>
                        <?php echo htmlspecialchars($ticket->description)?>
                    </p>
                </div>
            </div>

            <!-- Ticket Changes and Comments -->
            <?php foreach ($all as $item) : ?>
                <!-- Change --> 
                <?php if (($item instanceof AssignedChange) === true) : ?> 
                        <div class="event">
                            <img class="avatar" src="
                                <?php echo htmlspecialchars($item->user->image)?>
                            " alt="user">
                            <div>
                                <h4>
                                    <?php echo htmlspecialchars($item->user->displayName)?>
                                </h4>
                                <?php if ($item->agent->username === "") : ?>
                                    <p>remove <b>assigned</b> from ticket</p>
                                <?php else : ?>
                                <p><b>assign</b> this ticket to 
                                    <b><?php echo htmlspecialchars($item->agent->displayName)?></b>
                                </p>
                                <?php endif; ?>
                                <p>
                                    <?php echo htmlspecialchars(time_ago($item->timestamp))?>
                                </p>
                            </div>
                        </div>
                <?php ; elseif (($item instanceof StatusChange) === true) :?> 
                    <div class="event">
                        <img class="avatar" src="
                            <?php echo htmlspecialchars($item->user->image)?>
                        " alt="user">
                        <div>
                            <h4>
                                <?php echo htmlspecialchars($item->user->displayName)?>
                            </h4>
                            <?php if ($item->status === "closed") : ?>
                                <p><b>closed</b> this ticket</p>
                            <?php elseif ($item->status === "open") : ?>
                                <p><b>reopened</b> this ticket</p>
                            <?php else : ?>
                                <p>change status to <b>
                                    <?php echo $item->status ?>
                                </b></p>
                            <?php endif; ?>
                            <p>
                                <?php echo htmlspecialchars(time_ago($item->timestamp))?>
                            </p>
                        </div>
                    </div>
                <!-- Comment --> 
                <?php ; elseif (($item instanceof Comment) === true) :?> 
                    <div class="ticket-comment">
                        <div class="user-comment">
                            <div class="user">
                                <img class="avatar" src="
                                    <?php echo htmlspecialchars($item->createdByUser->image)?>
                                " alt="user" />
                                <h3><?php echo htmlspecialchars($item->createdByUser->displayName)?></h3>
                                <p>
                                    <?php echo htmlspecialchars(time_ago($item->timestamp))?>
                                </p>
                            </div>
                            <p>
                                <?php echo htmlspecialchars($item->content)?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- Closed with FAQ -->
            <?php if ($ticket->faq->id !== 0) :
                $id = $ticket->faq->id;
                ?>
                <div class="ticket-comment">
                    <div class="user-comment">
                        <h3>
                            <i class="ri-question-line"></i>
                            Ticket closed with FAQ <a href="/faq/<?php echo htmlspecialchars($id) ?>">#<?php echo htmlspecialchars($id)?></a>
                        </h3>
                        <?php
                        $content = explode("\n", $ticket->faq->content);
                        foreach ($content as $paragraph) : ?>
                            <p>
                                <?php echo htmlspecialchars($paragraph)?>
                            </p>
                        <?php endforeach; ?>
                        <h3>
                            From original question: 
                        </h3>
                        <p class="original-question">
                            <?php echo htmlspecialchars($ticket->faq->title)?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <form method="post" action="ticket?id=<?php echo htmlspecialchars($ticket->id)?>">
            <div class="comment-box top-line">
                <h3>New comment</h3>
                <textarea name="content" cols="30" rows="10" placeholder="Write your comment"></textarea>
                <input type="hidden" name="ticketId" value="<?php echo htmlspecialchars($ticket->id)?>">
                <input type="hidden" name="action" value="comment">
                <input type="submit" class="primary" value="Send">
            </div>
        </form>

        <div class="action-panel">
            <div class="side-card">
                <h4 class="task-label">Assignee</h4>
                <?php if ($ticket->assignee !== null) : ?>
                    <div class="user">
                        <div>
                            <img class="avatar" src="<?php echo htmlspecialchars($ticket->assignee->image)?>" alt="user">
                            <?php echo htmlspecialchars($ticket->assignee->displayName)?>
                        </div>
                        <?php if ($loggedUser->type === "admin" || $loggedUser->type === "agent") : ?>
                            <form method="POST" action="
                                <?php echo htmlspecialchars("ticket?id=$ticket->id")?>
                            ">
                                <input type="hidden" name="action" value="unassign">
                                <input type="hidden" name="ticketId" value="<?php echo htmlspecialchars($ticket->id)?>">
                                <i class="ri-close-line" onclick="submitFatherForm(event, this)"></i>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                <p onclick="makeUserAssignModal(
                    <?php
                        echo "'$loggedUser->type'";
                    ?>
                )">
                    <i class="ri-account-circle-line"></i>
                    Unassigned
                </p>
                <?php endif; ?>
            </div>
            <div class="side-card">
                <h4 class="task-label">Labels</h4>
                <div>
                    <?php if (count($ticket->labels) === 0) : ?>
                    <p onclick="makeLabelsModal(
                        <?php
                            echo "'$loggedUser->type'";
                        ?> 
                    )">
                        <i class="ri-price-tag-3-line"></i>
                        No labels assigned
                    </p>
                    <?php else :?>
                        <div class="tag-list" onclick="makeLabelsModal(
                            <?php
                            echo "'$loggedUser->type'";
                            ?> )">

                        <?php foreach ($ticket->labels as $label) :
                            $labelName            = htmlspecialchars($label->label);
                            $labelColor           = htmlspecialchars($label->color);
                            $labelBackgroundColor = htmlspecialchars($label->backgroundColor);?>

                            <div class="tag" style="color: <?php echo htmlspecialchars($labelColor)?>; 
                                    background-color: <?php echo htmlspecialchars($labelBackgroundColor)?>;" onclick="makeEditModal('editLabel',this)">
                                    <p style="color: <?php echo htmlspecialchars($labelColor)?>;"><?php echo htmlspecialchars($labelName)?></p>
                            </div>
                        <?php endforeach;
                        ?></div>
                    <?php endif;?>
                </div>
            </div>
            <div class="side-card">
                <h4 class="task-label">Department</h4>
                <?php if ($ticket->department !== "") : ?>
                        <div class="user">
                            <div>
                                <span><i class="ri-building-4-line"></i></span>
                                <?php echo htmlspecialchars($ticket->department)?>
                            </div>
                            <?php if ($loggedUser->type === "admin" || $loggedUser->type === "agent") : ?>
                                <form method="POST" action="
                                    <?php echo htmlspecialchars("ticket?id=$ticket->id")?>
                                ">
                                    <input type="hidden" name="action" value="changeDepartment">
                                    <input type="hidden" name="ticketId" value="<?php echo htmlspecialchars($ticket->id)?>">
                                    <i class="ri-close-line" onclick="submitFatherForm(event, this)"></i>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php else : ?>
                    <p onclick="makeDepartmentAssignModal('<?php echo htmlspecialchars(htmlspecialchars($loggedUser->type))?>')">
                        <i class="ri-account-circle-line"></i>
                        Unassigned
                    </p>
                    <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
