<?php

require_once __DIR__."/utils/logger.php";

$pagesDir = __DIR__.'/pages';
$apiDir   = __DIR__.'/api';

$contentExp = "/\.(?:3gp|apk|avi|bmp|css|csv|doc|docx|flac|gif|gz|gzip|htm|html|ics|jpe|jpeg|jpg|js|kml|kmz|m4a|mov|mp3|mp4|mpeg|mpg|odp|ods|odt|oga|ogg|ogv|pdf|pdf|png|pps|pptx|qt|svg|swf|tar|text|tif|txt|wav|webm|wmv|xls|xlsx|xml|xsl|xsd|zip|woff|woff2|ttf|map)(?:\?.*)?$/";
$apiExp     = "/^\/api\/([[:alnum:]]*)(\/.*)?(?:\?.*)?$/";
$exp        = "/^\/([[:alnum:]]*)(\/.*)?(?:\?.*)?$/";
$matches    = [];


function api_route_error()
{
    log_to_stdout($_SERVER["REQUEST_METHOD"]." - ".$_SERVER["REQUEST_URI"]." - no such api route.", "e");
    header("Content-Type: application/json");
    http_response_code(404);
    echo '{"error":"api route not found..."}';

}


if (preg_match($contentExp, $_SERVER["REQUEST_URI"]) === 1) {
    return false; //return content as is
}

if ($_SERVER["REQUEST_URI"] === "/") {
    include "pages/home.php";
} else if (preg_match($apiExp, $_SERVER["REQUEST_URI"], $matches) !== 0) {
    $fullpath = $apiDir."/".$matches[1].".php";
    if (is_file($fullpath) === true) {
        log_to_stdout($_SERVER["REQUEST_METHOD"]." - ".$_SERVER["REQUEST_URI"]." - api route.", "s");
        try {
            include $fullpath;
        } catch (Exception $e) {
            log_to_stdout($_SERVER["REQUEST_METHOD"]." - ".$_SERVER["REQUEST_URI"]." ERROR:".$e->getMessage(), "e");
            log_to_stdout($e->getTraceAsString(), "e");
        }
    } else {
        api_route_error();
    }
} else if (preg_match($exp, $_SERVER["REQUEST_URI"], $matches) !== 0) {
    $fullpath = $pagesDir."/".$matches[1].".php";
    if (is_file($fullpath) === true) {
        log_to_stdout($_SERVER["REQUEST_METHOD"]." - ".$_SERVER["REQUEST_URI"]." - page route.", "s");
        try {
            include $fullpath;
        } catch (Exception $e) {
            log_to_stdout($_SERVER["REQUEST_METHOD"]." - ".$_SERVER["REQUEST_URI"]." ERROR:".$e->getMessage(), "e");
            log_to_stdout($e->getTraceAsString(), "e");
        }
    } else {
        if (preg_match("/^\/api\//", $_SERVER["REQUEST_URI"]) === 1) {
            api_route_error();
            exit();
        } else {
            include $pagesDir."/not-found.php";
            log_to_stdout($_SERVER["REQUEST_METHOD"]." - ".$_SERVER["REQUEST_URI"]." - no such page route.", "e");
        }
    }
}
