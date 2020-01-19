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

    $current_user = utils\currentUser();
    if (!$current_user) {
        return Response::ok('home/index.phtml', $variables);
    }

    $cycle_dao = new models\dao\Cycle();
    $running_cycle_values = $cycle_dao->findRunningForUser($current_user->id);

    if (!$running_cycle_values) {
        if ($current_user->onboarding_step === 0) {
            return Response::redirect('cycles#preferences');
        } else {
            return Response::redirect('cycles#starting');
        }
    }

    $variables['current_cycle'] = new models\Cycle($running_cycle_values);
    return Response::ok('home/dashboard.phtml', $variables);
}
