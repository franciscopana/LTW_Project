<?php

declare(strict_types=1);


function parse_env_file(string $filePath) : array
{
    $envFilePattern = "/([[:word:]]*)\ +=\ +(.*)/";

    if (is_file($filePath) === false) {
        return [];
    }

    $file = fopen($filePath, "r");
    if ($file === false) {
        return [];
    }

    $resultArray = [];
    while (($line = fgets($file)) !== false) {
        preg_match($envFilePattern, $line, $matches);
        $resultArray[$matches[1]] = $matches[2];
    }

    return $resultArray;

}
