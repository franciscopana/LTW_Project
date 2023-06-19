<?php
require_once 'components/navbar/navbar.php';
require_once 'database/database.php';
?>

<?php function layout_start()
{
    ?>
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                <title>Tickets</title>
                <link rel="stylesheet" href="/css/layout.css">
                <link rel="stylesheet" href="/css/components.css">
                <link rel="stylesheet" href="/css/theme.css">
                <link rel="stylesheet" href="/css/remixicon.css">
            </head>
            <body>
    <?php

                $db = get_database();
                echo navbar($db);
    ?>
                <main>
    <?php

} ?>



<?php function layout_end()
{
    ?>
                </main>
            </body>
        </html>
        <?php

}
