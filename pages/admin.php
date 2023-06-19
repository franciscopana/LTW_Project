<?php
require_once __DIR__.'/../database/database.php';
require_once __DIR__.'/../utils/roles.php';
require_once __DIR__.'/../utils/session.php';
require_once __DIR__.'/../components/navbar/navbar.php';
require_once __DIR__.'/../database/client.db.php';
require_once __DIR__.'/../database/status.db.php';
require_once __DIR__.'/../database/labels.db.php';

require_once __DIR__.'/../database/department.db.php';
require_once __DIR__.'/../components/user-table/user-table.php';
require_once __DIR__.'/../components/department-table/department-table.php';




$db      = get_database();
$session = is_session_valid($db);
if ($session === null) {
    header("Location: /login");
    exit();
}

if (is_current_user_admin($db) === false) {
    header("Location: /");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    //TODO: check for csrf token
    if (isset($_POST["action"]) === false) {
        header("Location: /");
        exit();
    }


    if ($_POST["action"] === "deleteUser" && isset($_POST["username"]) === true) {
        if ($_POST["username"] !== $session->username) {
            delete_client($_POST["username"], $db);
        }

        //TODO: make error message
    }

    if ($_POST["action"] === "editUser" && isset($_POST["username"]) === true) {
        if (update_user($_POST["username"], $_POST["displayName"], ($_POST["password"] ?? ""), $_POST["email"], $_POST["role"], $db) === false) {
            log_to_stdout("Error while updating user ".$_POST["username"], "e");
            //TODO: make error message
        }
    }

    if ($_POST["action"] === "newDepartment") {
        $members = explode(",", ($_POST["members"] ?? ''));
        if ($members === false || (count($members) === 1 && $members[0] === '')) {
            $members = [];
        }

        add_department($_POST["name"], $_POST["description"], $members, $db);
    }


    if ($_POST["action"] === "editDepartment") {
        edit_department($_POST["name"], $_POST["description"], $db);
    }

    if ($_POST["action"] === "deleteDepartment") {
        delete_department($_POST["name"], $db);
    }

    if ($_POST["action"] === "addLabel") {
        add_label($_POST["name"], $_POST["color"], $_POST["backgroundColor"], $db);
    }

    if ($_POST["action"] === "addStatus") {
        add_status($_POST["name"], $_POST["color"], $_POST["backgroundColor"], $db);
    }

    if ($_POST["action"] === "editStatus") {
        edit_status($_POST["name"], $_POST["color"], $_POST["backgroundColor"], $db);
    }

    if ($_POST["action"] === "editLabel") {
        edit_label($_POST["name"], $_POST["color"], $_POST["backgroundColor"], $db);
    }

    if ($_POST["action"] === "deleteLabel") {
        delete_label($_POST["name"], $db);
    }

    if ($_POST["action"] === "deleteStatus") {
        delete_status($_POST["name"], $db);
    }

    if ($_POST["action"] === "addMember") {
        //TODO: verify that user exists
        //TODO: verify that user is not already in department
        //TODO: verify department exists
        add_member_to_department($_POST["departmentId"], $_POST["user"], $db);
    }

    if ($_POST["action"] === "removeMember" && isset($_POST["department"]) === true && isset($_POST["user"]) === true) {
        remove_member_to_department($_POST["department"], $_POST["user"], $db);
    }


    if (isset($_POST["lastHref"]) === true) {
        header("Location: ".$_POST["lastHref"]);
    } else {
        header("Location: /admin");
    }

    exit();
}

$limit  = min(intval(($_GET["limit"] ?? 10)), 20);
$offset = intval(($_GET["offset"] ?? 0));
$tab    = ($_GET["tab"] ?? "users");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets</title>
    <link rel="stylesheet" href="/css/layout.css">
    <link rel="stylesheet" href="/css/theme.css">
    <link rel="stylesheet" href="/css/remixicon.css">
</head>
<body>
    <?php
    navbar($db);

    ?>
<main>
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/modal.css">
    <link rel="stylesheet" href="/css/dropdown.css">
    <link rel="stylesheet" href="/css/components.css">
    <script src="/js/modal.js"></script>


    <h1>Admin page</h1>
    <ul class="tabSelector">
        <li <?php
        if ($tab === "users") {
            echo 'class="active"';
        }?>>
            <a href="?tab=users">Users</a>
        </li>
        <li
        <?php
        if ($tab === "departments") {
            echo 'class="active"';
        }?>>
            <a href="?tab=departments">Departments</a>
        </li>
        <li 
        <?php
        if ($tab === "label-status") {
            echo 'class="active"';
        }?>>
            <a href="?tab=label-status">Labels & Status</a>
        </li>
    </ul>

    <?php if ($tab === "users") :?>
        <?php
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
        ?>
        <script src="/js/user-table.js"></script>

        <?php
        drawUserTable($clients);
        elseif ($tab === "departments") :
            $departments = get_departments($limit, $offset, $db, true);
            ?>
            <script src="/js/department.js"></script>

            <div class="department-buttons">
                <button onclick="makeAddDepartmentModal()" class="primary">Add new department</button>
            </div>
            <?php drawDepartmentTable($departments);
        endif;?>
    
    <?php if ($tab === "label-status") :
        $labels   = get_all_labels($db);
        $statuses = get_all_status($db);?>
        <link rel="stylesheet" href="/css/label-status.css">
        <script src="/js/label-status.js"></script>

        <div class="label-status-button">
            <button class="primary" onclick="makeNewModal('addLabel', 'Add new label')">Add new label</button>
            <button class="primary" onclick="makeNewModal('addStatus', 'Add new status')">Add new status</button>
        </div>      
        <h2>Statuses</h2>
        <div class="status-list">
            <?php foreach ($statuses as $status) :
                $statusName            = htmlspecialchars($status->status);
                $statusColor           = htmlspecialchars($status->color);
                $statusBackgroundColor = htmlspecialchars($status->backgroundColor);?>
                <div class="tag" style="color: <?php echo htmlspecialchars($statusColor)?>; 
                        background-color: <?php echo htmlspecialchars($statusBackgroundColor)?>;" onclick="makeEditModal('editStatus',this)">
                        <p><?php echo htmlspecialchars($statusName)?></p>
                </div>
                
            <?php endforeach;?>
            
        </div>  
        <h2>Labels</h2>
        <div class="label-list">
            <?php foreach ($labels as $label) :
                $labelName            = htmlspecialchars($label->label);
                $labelColor           = htmlspecialchars($label->color);
                $labelBackgroundColor = htmlspecialchars($label->backgroundColor);?>
                <div class="tag" style="color: <?php echo htmlspecialchars($labelColor)?>; 
                        background-color: <?php echo htmlspecialchars($labelBackgroundColor)?>;" onclick="makeEditModal('editLabel',this)">
                        <p><?php echo htmlspecialchars($labelName)?></p>
                </div>
            <?php endforeach;?>
        </div>  
        
    <?php endif;?>
        
</main>    
</body>
</html>
