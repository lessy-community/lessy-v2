<?php

namespace Lessy\controllers\home;

use Minz\Response;
use Lessy\utils;

function index($request)
{
    $variables = [
        'available_locales' => utils\Locale::availableLocales(),
        'current_locale' => utils\Locale::currentLocale(),
    ];

    return Response::ok('home/index.phtml', $variables);
}
