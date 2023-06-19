<?php
    require_once 'components/navbar/navbar.php';
    require_once 'database/database.php';
    require_once 'utils/action_ticket.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #2</title>
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/remixicon.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/ticket.css">
</head>
<body>
    <?php
        $db = get_database();
        echo navbar($db);

    if (is_session_valid($db) === null) {
        header('Location: /login');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title       = $_POST['title'];
        $description = $_POST['description'];
        $created     = create_ticket($title, $description, $db);
        if ($created === false) {
            // Um handle qualquer, qual?
        }

        exit();
    }
    ?>
    <main class="ticket-page">
        <div>
            <h1>Creating a new Ticket</h1>
            <form method="POST" action="newTicket">
                <div class="comment-box">
                    <h3>Title</h3>
                    <input type="text" placeholder="Title" name="title">
                    <h3>Description</h3>
                    <textarea cols="30" rows="10" placeholder="Describe your issue" name="description"></textarea>
                    <input type="submit" class="primary" value="Create" />
                </div>
            </form>
        </div>
    </main>
</body>
</html>
