<?php


function time_ago(Datetime $datetime)
{
    $datetime->setTimezone(new DateTimeZone('Europe/Lisbon'));
    $diff = (time() - $datetime->getTimestamp());

    if ($diff < 1) {
        return 'now';
    }

    $a      = [
        (365 * 24 * 60 * 60) => 'year',
        (30 * 24 * 60 * 60)  => 'month',
        (24 * 60 * 60)       => 'day',
        (60 * 60)            => 'hour',
        60                   => 'minute',
        1                    => 'second',
    ];
    $plural = [
        'year'   => 'years',
        'month'  => 'months',
        'day'    => 'days',
        'hour'   => 'hours',
        'minute' => 'minutes',
        'second' => 'seconds',
    ];

    foreach ($a as $secs => $str) {
        $d = ($diff / $secs);
        if ($d >= 1) {
            $r = round($d);
            if ($r > 1) {
                return $r.' '.$plural[$str].' ago';
            } else {
                return $r.' '.$str.' ago';
            }
        }
    }

}
