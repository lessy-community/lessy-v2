<?php

namespace Lessy\controllers\home;

use Minz\Response;
use Lessy\utils;
use Lessy\models;

function index($request)
{
    $variables = [
        'available_locales' => utils\Locale::availableLocales(),
        'current_locale' => utils\Locale::currentLocale(),
    ];

    $status = $request->param('status');
    if ($status === 'connected') {
        $variables['success'] = _('You’re now connected, welcome back!');
    } elseif ($status === 'deconnected') {
        $variables['success'] = _('You’re now disconnected, see you!');
    }

    $current_user = utils\currentUser();
    $cycle_dao = new models\dao\Cycle();
    $no_cycles = $current_user && $cycle_dao->countForUser($current_user->id) === 0;

    if ($no_cycles) {
        if ($current_user->onboarding_step === 0) {
            return Response::redirect('cycles#preferences');
        } else {
            return Response::redirect('cycles#starting');
        }
    } elseif ($current_user) {
        return Response::ok('home/dashboard.phtml', $variables);
    } else {
        return Response::ok('home/index.phtml', $variables);
    }
}
