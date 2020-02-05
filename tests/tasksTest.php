<?php

namespace Lessy\controllers\home;

use Minz\Tests\IntegrationTestCase;
use Lessy\tests;
use Lessy\models;

class TasksTest extends IntegrationTestCase
{
    /**
     * @after
     */
    public function logout()
    {
        tests\utils\logout();
    }

    public function testIndex()
    {
        $user_id = tests\utils\login();
        $task_id = self::$factories['tasks']->create([
            'user_id' => $user_id,
        ]);
        $request = new \Minz\Request('GET', '/tasks');

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
        $variables = $response->output()->variables();
        $this->assertArrayHasKey('tasks', $variables);
        $tasks = $variables['tasks'];
        $this->assertSame(1, count($tasks));
        $this->assertSame($task_id, $tasks[0]->id);
    }

    public function testIndexConsidersPriority()
    {
        $user_id = tests\utils\login();
        $second_task_id = self::$factories['tasks']->create([
            'user_id' => $user_id,
            'priority' => 2,
        ]);
        $first_task_id = self::$factories['tasks']->create([
            'user_id' => $user_id,
            'priority' => 1,
        ]);
        $request = new \Minz\Request('GET', '/tasks');

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
        $variables = $response->output()->variables();
        $this->assertArrayHasKey('tasks', $variables);
        $tasks = $variables['tasks'];
        $this->assertSame(2, count($tasks));
        $this->assertSame($first_task_id, $tasks[0]->id);
        $this->assertSame($second_task_id, $tasks[1]->id);
    }

    public function testIndexDoesNotListTaskOfOtherUsers()
    {
        $user_id = tests\utils\login();
        $other_user_id = self::$factories['users']->create();
        $other_task_id = self::$factories['tasks']->create([
            'user_id' => $other_user_id,
        ]);
        $request = new \Minz\Request('GET', '/tasks');

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
        $variables = $response->output()->variables();
        $this->assertSame(0, count($variables['tasks']));
    }

    public function testIndexWhenUnconnected()
    {
        $request = new \Minz\Request('GET', '/tasks');

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/login?from=tasks%23index']);
    }
}
