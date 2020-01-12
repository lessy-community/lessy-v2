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

    $status = $request->param('status');
    if ($status === 'registered') {
        $variables['success'] = _('Your account has been created, welcome!');
    } elseif ($status === 'connected') {
        $variables['success'] = _('You’re now connected, welcome back!');
    }

    return Response::ok('home/index.phtml', $variables);
}
