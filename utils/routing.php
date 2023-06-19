<?php

declare(strict_types=1);

require_once __DIR__."/logger.php";


function handle_api_route(string $path, string $method, Closure $handler) : void
{
    $originalPath  = $path;
    $matches       = [];
    $argumentRegex = "/<([[:alnum:]]+)>/";
    if (preg_match($argumentRegex, $path, $matches) !== 0) {
        array_shift($matches);
    }

    $path = preg_replace($argumentRegex, "((?:[[:alnum:]]|\ )*)", $path);
    $path = preg_replace("/\//", "\/", $path);

    $reflection = new ReflectionFunction($handler);
    $arguments  = $reflection->getParameters();

    if (count($arguments) !== count($matches)) {
        throw new ErrorException($originalPath." handler doesnt have the correct number of arguments...");
    }

    $contains = array_map(
        function ($a, $b) {
            return $a->name == $b;
        },
        $arguments,
        $matches
    );

    $containsAllParameters = array_reduce(
        $contains,
        function ($a, $b) {
            return $a && $b;
        },
        true
    );

    if ($containsAllParameters === false) {
        throw new ErrorException($originalPath." handler doesnt have the correct arguments or is in the wrong order...");
    }

    //append to regex the url parms and the / after the string
    $pathRegex        = "/".$path."\/?(?:\?.*)?$/";
    $closureArguments = [];
    //remove /api regex
    if (preg_match($pathRegex, substr($_SERVER["REQUEST_URI"], 4), $closureArguments) === 1) {
        if ($_SERVER["REQUEST_METHOD"] === $method) {
            array_shift($closureArguments);
            call_user_func_array($handler, $closureArguments);
            exit();
        }
    }

}


function no_api_route() : void
{
    log_to_stdout($_SERVER["REQUEST_METHOD"]." - ".$_SERVER["REQUEST_URI"]." has no api handler on ".__FILE__, "e");
    http_response_code(403);
    echo '{"error":"no suitable api route handler..."}';
    exit();

}


function handle_page_route(string $path, string $fileName) : void
{
    $matches       = [];
    $argumentRegex = "/<([[:alnum:]]+)>/";
    if (preg_match($argumentRegex, $path, $matches) !== 0) {
        array_shift($matches);
    }

    $path = preg_replace($argumentRegex, "([[:alnum:]]*)", $path);
    $path = preg_replace("/\//", "\/", $path);

    //append to regex the url parms and the / after the string
    $pathRegex = "/".$path."\/?(?:\?.*)?$/";

    //match only the uri after
    //TODO: maybe handle elements?
    if (preg_match($pathRegex, $_SERVER["REQUEST_URI"]) === 1) {
        include $fileName;
        exit();
    }

}
