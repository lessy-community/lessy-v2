<?php

namespace Lessy\controllers\cycles;

use Minz\Response;
use Minz\Errors;
use Minz\CSRF;
use Lessy\utils;
use Lessy\models;

function preferences($request)
{
    $current_user = utils\currentUser();
    if (!$current_user) {
        return Response::redirect('auth#login', ['from' => 'cycles#preferences']);
    }

    return Response::ok('cycles/preferences.phtml', [
        'work_weeks' => $current_user->cycles_work_weeks,
        'rest_weeks' => $current_user->cycles_rest_weeks,
        'start_day' => $current_user->cycles_start_day,
    ]);
}

function update_preferences($request)
{
    $current_user = utils\currentUser();
    if (!$current_user) {
        return Response::redirect('auth#login', ['from' => 'cycles#preferences']);
    }

    $work_weeks = $request->param('work_weeks');
    $rest_weeks = $request->param('rest_weeks');
    $start_day = $request->param('start_day');

    $csrf = new CSRF();
    if (!$csrf->validateToken($request->param('csrf'))) {
        return Response::badRequest('cycles/preferences.phtml', [
            'work_weeks' => $work_weeks,
            'rest_weeks' => $rest_weeks,
            'start_day' => $start_day,
            'error' => _('A security verification failed, you should submit the form again.'),
        ]);
    }

    try {
        $current_user->setProperty('cycles_work_weeks', $work_weeks);
        $current_user->setProperty('cycles_rest_weeks', $rest_weeks);
        $current_user->setProperty('cycles_start_day', $start_day);
    } catch (Errors\ModelPropertyError $e) {
        $errors = [];
        if ($e->property() === 'cycles_work_weeks') {
            $errors['work_weeks'] = _('This number of week is invalid.');
        } elseif ($e->property() === 'cycles_rest_weeks') {
            $errors['rest_weeks'] = _('This number of week is invalid.');
        } elseif ($e->property() === 'cycles_start_day') {
            $errors['start_day'] = _('This day is invalid.');
        } else {
            $errors[$e->property()] = $e->getMessage();
        }
        return Response::badRequest('cycles/preferences.phtml', [
            'work_weeks' => $work_weeks,
            'rest_weeks' => $rest_weeks,
            'start_day' => $start_day,
            'errors' => $errors,
        ]);
    }

    $user_dao = new models\dao\User();
    try {
        $user_dao->save($current_user);
        return Response::redirect('cycles#starting');
    } catch (Errors\DatabaseModelError $e) {
        return Response::internalServerError('cycles/preferences.phtml', [
            'work_weeks' => $work_weeks,
            'rest_weeks' => $rest_weeks,
            'start_day' => $start_day,
            'error' => _('We were unable to save your preferences for an unknown reason. Please contact the support.'),
        ]);
    }
}

function starting($request)
{
    return Response::ok('cycles/starting.phtml');
}
