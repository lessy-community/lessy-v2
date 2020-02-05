<?php

namespace Lessy\controllers\tasks;

use Minz\Response;
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
