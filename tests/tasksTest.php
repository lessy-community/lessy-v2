<?php

namespace Lessy\controllers\home;

use Minz\Tests\IntegrationTestCase;
use Minz\CSRF;
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

    public function testCreate()
    {
        $task_dao = new models\dao\Task();
        $user_id = tests\utils\login();
        $request = new \Minz\Request('POST', '/tasks', [
            'csrf' => (new CSRF())->generateToken(),
            'label' => 'A new task',
        ]);

        $this->assertSame(0, $task_dao->count());

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/tasks']);
        $this->assertSame(1, $task_dao->count());
        $task = $task_dao->listAll()[0];
        $this->assertSame('A new task', $task['label']);
    }

    public function testCreateIncrementsPriority()
    {
        $task_dao = new models\dao\Task();
        $user_id = tests\utils\login();
        self::$factories['tasks']->create([
            'user_id' => $user_id,
            'priority' => 1,
        ]);
        $request = new \Minz\Request('POST', '/tasks', [
            'csrf' => (new CSRF())->generateToken(),
            'label' => 'A new task',
        ]);

        $response = self::$application->run($request);

        $this->assertSame(2, $task_dao->count());
        $task = $task_dao->listAll()[1];
        $this->assertSame(2, (int)$task['priority']);
    }

    public function testCreateWhenUnconnected()
    {
        $request = new \Minz\Request('POST', '/tasks', [
            'csrf' => (new CSRF())->generateToken(),
            'label' => 'A new task',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/login?from=tasks%23index']);
    }

    public function testCreateFailsIfLabelIsEmpty()
    {
        $task_dao = new models\dao\Task();
        $user_id = tests\utils\login();
        $request = new \Minz\Request('POST', '/tasks', [
            'csrf' => (new CSRF())->generateToken(),
            'label' => '',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 400);
        $this->assertSame(0, $task_dao->count());
        $variables = $response->output()->variables();
        $this->assertNotEmpty($variables['errors']['label']);
    }

    public function testCreateFailsIfCsrfIsInvalid()
    {
        $task_dao = new models\dao\Task();
        $user_id = tests\utils\login();
        (new CSRF())->generateToken();
        $request = new \Minz\Request('POST', '/tasks', [
            'csrf' => 'not the token',
            'label' => 'A new task',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 400);
        $this->assertSame(0, $task_dao->count());
        $variables = $response->output()->variables();
        $this->assertNotEmpty($variables['error']);
    }
}
