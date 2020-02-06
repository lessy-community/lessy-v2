<?php

namespace Lessy\controllers\tasks;

use Minz\Response;
use Minz\CSRF;
use Lessy\utils;
use Lessy\models;

function index($request)
{
    $current_user = utils\currentUser();
    if (!$current_user) {
        return Response::redirect('auth#login', ['from' => 'tasks#index']);
    }

    $task_dao = new models\dao\Task();
    $tasks = $task_dao->listForUser($current_user->id);
    return Response::ok('tasks/index.phtml', [
        'tasks' => models\Task::daoToTasks($tasks),
    ]);
}

function create($request)
{
    $current_user = utils\currentUser();
    if (!$current_user) {
        return Response::redirect('auth#login', ['from' => 'tasks#index']);
    }

    $task_dao = new models\dao\Task();
    $tasks = $task_dao->listForUser($current_user->id);

    $label = $request->param('label');

    $csrf = new CSRF();
    if (!$csrf->validateToken($request->param('csrf'))) {
        return Response::badRequest('tasks/index.phtml', [
            'label' => $label,
            'tasks' => models\Task::daoToTasks($tasks),
            'error' => _('A security verification failed, you should submit the form again.'),
        ]);
    }

    try {
        $task = models\Task::new($current_user->id, $label);
        $priority = $task_dao->highestPriorityForUser($current_user->id) + 1;
        $task->setProperty('priority', $priority);
    } catch (\Minz\Errors\ModelPropertyError $e) {
        if ($e->property() === 'label') {
            $errors = ['label' => _('The label is required.')];
        } else {
            $errors = [$e->property() => $e->getMessage()];
        }

        return Response::badRequest('tasks/index.phtml', [
            'label' => $label,
            'tasks' => models\Task::daoToTasks($tasks),
            'errors' => $errors,
        ]);
    }

    try {
        $task_dao->save($task);
        return Response::redirect('tasks#index');
    } catch (Errors\DatabaseModelError $e) {
        return Response::internalServerError('tasks/index.phtml', [
            'label' => $label,
            'tasks' => models\Task::daoToTasks($tasks),
            'error' => _('We were unable to create your task for an unknown reason. Please contact the support.'),
        ]);
    }
}
