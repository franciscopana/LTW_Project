<?php
require_once 'components/layout/layout.php';

http_response_code(404);

layout_start();

?>

<!DOCTYPE html>
<head>
    <title>Page not found</title>
    <link rel="stylesheet" href="/css/not-found.css">
</head>
<html>
    <body>
        <h1>Page not found</h1>
        <img src="assets/images/error404.svg">
    </body>
</html>
