<?php
declare(strict_types=1);


function log_to_stdout(string $string, $type='i'):void
{
    $out   = fopen('php://stdout', 'w');
    $color = "";
    switch ($type) {
    case 'e':
        $color = "\033[31m";
        break;
    case 's':
        $color = "\033[32m";
        break;
    case 'w':
        $color = "\033[33m";
        break;
    case 'i':
        $color = "\033[36m";
        break;
    }

    fputs($out, $color.$string."\033[0m\n");
    fclose($out);

}
