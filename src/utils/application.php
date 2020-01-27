<?php

namespace Lessy\utils;

function currentUser()
{
    return \Lessy\models\dao\User::currentUser();
}

function javascriptConfiguration()
{
    return json_encode([
        'l10n' => [
            'months' => monthLabels(),
            'weekDays' => weekDayLabels(),
        ],
    ]);
}

function monthLabels()
{
    $months = [];
    foreach (range(1, 12) as $month_index) {
        $date = \DateTime::createFromFormat('n', $month_index);
        $months[] = strftime('%B', $date->getTimestamp());
    }
    return $months;
}

function weekDayLabels()
{
    $days = [];
    $reference_date = new \DateTime('1992-01-20'); // It was a Monday
    for ($i = 0; $i < 7; $i++) {
        $days[] = strftime('%a', $reference_date->getTimestamp());
        $reference_date->modify('+1 day');
    }
    return $days;
}
