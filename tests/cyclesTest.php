<?php

namespace Lessy\controllers\home;

use Minz\Tests\IntegrationTestCase;
use Minz\CSRF;
use Lessy\tests;
use Lessy\models;

class CyclesTest extends IntegrationTestCase
{
    /**
     * @after
     */
    public function logout()
    {
        tests\utils\logout();
    }

    public function testPreferences()
    {
        $user_id = tests\utils\login([
            'cycles_work_weeks' => 5,
            'cycles_rest_weeks' => 2,
            'cycles_start_day' => 'sunday',
        ]);
        $request = new \Minz\Request('GET', '/cycles/preferences');

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
        $variables = $response->output()->variables();
        $this->assertSame(5, $variables['work_weeks']);
        $this->assertSame(2, $variables['rest_weeks']);
        $this->assertSame('sunday', $variables['start_day']);
    }

    public function testPreferencesWhenUnconnected()
    {
        $request = new \Minz\Request('GET', '/cycles/preferences');

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/login?from=cycles%23preferences']);
    }

    public function testUpdatePreferences()
    {
        $user_dao = new \Lessy\models\dao\User();
        $user_id = tests\utils\login([
            'cycles_work_weeks' => 4,
            'cycles_rest_weeks' => 1,
            'cycles_start_day' => 'monday',
        ]);
        $request = new \Minz\Request('POST', '/cycles/preferences', [
            'csrf' => (new CSRF())->generateToken(),
            'work_weeks' => 5,
            'rest_weeks' => 2,
            'start_day' => 'sunday',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/cycles/starting']);
        $user_values = $user_dao->find($user_id);
        $this->assertSame(5, (int)$user_values['cycles_work_weeks']);
        $this->assertSame(2, (int)$user_values['cycles_rest_weeks']);
        $this->assertSame('sunday', $user_values['cycles_start_day']);
    }

    public function testUpdatePreferencesWhenUnconnected()
    {
        $request = new \Minz\Request('POST', '/cycles/preferences', [
            'csrf' => (new CSRF())->generateToken(),
            'work_weeks' => 5,
            'rest_weeks' => 2,
            'start_day' => 'sunday',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/login?from=cycles%23preferences']);
    }

    public function testUpdatePreferencesFailsIfWorkWeeksIsInvalid()
    {
        $user_dao = new \Lessy\models\dao\User();
        $user_id = tests\utils\login();
        $request = new \Minz\Request('POST', '/cycles/preferences', [
            'csrf' => (new CSRF())->generateToken(),
            'work_weeks' => 8, // max is 7
            'rest_weeks' => 2,
            'start_day' => 'sunday',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 400);
        $variables = $response->output()->variables();
        $this->assertSame(8, $variables['work_weeks']);
        $this->assertSame(2, $variables['rest_weeks']);
        $this->assertSame('sunday', $variables['start_day']);
        $this->assertArrayHasKey('work_weeks', $variables['errors']);
    }

    public function testUpdatePreferencesFailsIfRestWeeksIsInvalid()
    {
        $user_dao = new \Lessy\models\dao\User();
        $user_id = tests\utils\login();
        $request = new \Minz\Request('POST', '/cycles/preferences', [
            'csrf' => (new CSRF())->generateToken(),
            'work_weeks' => 5,
            'rest_weeks' => 3, // max is 2
            'start_day' => 'sunday',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 400);
        $variables = $response->output()->variables();
        $this->assertSame(5, $variables['work_weeks']);
        $this->assertSame(3, $variables['rest_weeks']);
        $this->assertSame('sunday', $variables['start_day']);
        $this->assertArrayHasKey('rest_weeks', $variables['errors']);
    }

    public function testUpdatePreferencesFailsIfStartDayIsInvalid()
    {
        $user_dao = new \Lessy\models\dao\User();
        $user_id = tests\utils\login();
        $request = new \Minz\Request('POST', '/cycles/preferences', [
            'csrf' => (new CSRF())->generateToken(),
            'work_weeks' => 5,
            'rest_weeks' => 2,
            'start_day' => 'tuesday', // only monday and sunday are accepted
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 400);
        $variables = $response->output()->variables();
        $this->assertSame(5, $variables['work_weeks']);
        $this->assertSame(2, $variables['rest_weeks']);
        $this->assertSame('tuesday', $variables['start_day']);
        $this->assertArrayHasKey('start_day', $variables['errors']);
    }

    public function testUpdatePreferencesFailsIfCsrfIsInvalid()
    {
        $user_dao = new \Lessy\models\dao\User();
        $user_id = tests\utils\login();
        (new CSRF())->generateToken();
        $request = new \Minz\Request('POST', '/cycles/preferences', [
            'csrf' => 'not the token',
            'work_weeks' => 5,
            'rest_weeks' => 2,
            'start_day' => 'sunday',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 400);
        $variables = $response->output()->variables();
        $this->assertSame(5, $variables['work_weeks']);
        $this->assertSame(2, $variables['rest_weeks']);
        $this->assertSame('sunday', $variables['start_day']);
        $this->assertNotEmpty($variables['error']);
    }

    public function testStarting()
    {
        \Minz\Time::freeze(date_create('2020-01-21 12:00')); // Tuesday
        $user_id = tests\utils\login([
            'cycles_work_weeks' => 4,
            'cycles_rest_weeks' => 1,
            'cycles_start_day' => 'monday',
        ]);
        $request = new \Minz\Request('GET', '/cycles/starting');

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
        $cycle = $response->output()->variables()['cycle'];
        $this->assertSame(4, $cycle->work_weeks);
        $this->assertSame(1, $cycle->rest_weeks);
        $this->assertSame(
            date_create('2020-01-20')->getTimestamp(),
            $cycle->start_at->getTimestamp()
        );
        $this->assertSame(
            date_create('2020-02-23')->getTimestamp(),
            $cycle->end_at->getTimestamp()
        );
    }

    public function testStartingWhenCurrentDayIsStartDay()
    {
        \Minz\Time::freeze(date_create('2020-01-20 12:00')); // Monday
        $user_id = tests\utils\login([
            'cycles_work_weeks' => 4,
            'cycles_rest_weeks' => 1,
            'cycles_start_day' => 'monday',
        ]);
        $request = new \Minz\Request('GET', '/cycles/starting');

        $response = self::$application->run($request);

        $cycle = $response->output()->variables()['cycle'];
        $this->assertSame(
            date_create('2020-01-20')->getTimestamp(),
            $cycle->start_at->getTimestamp()
        );
    }

    public function testStartingWhenUnconnected()
    {
        $request = new \Minz\Request('GET', '/cycles/starting');

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/login?from=cycles%23starting']);
    }

    public function testStart()
    {
        $cycle_dao = new models\dao\Cycle();
        \Minz\Time::freeze(date_create('2020-01-21 12:00')); // Tuesday
        $user_id = tests\utils\login([
            'cycles_work_weeks' => 4,
            'cycles_rest_weeks' => 1,
            'cycles_start_day' => 'monday',
        ]);
        $request = new \Minz\Request('POST', '/cycles/starting', [
            'csrf' => (new CSRF())->generateToken(),
        ]);

        $this->assertSame(0, $cycle_dao->count());

        $response = self::$application->run($request);

        $this->assertSame(1, $cycle_dao->count());

        $this->assertResponse($response, 302, null, ['Location' => '/']);
        $cycle = $cycle_dao->listAll()[0];
        $this->assertSame(4, (int)$cycle['work_weeks']);
        $this->assertSame(1, (int)$cycle['rest_weeks']);
        $this->assertSame(
            date_create('2020-01-20')->getTimestamp(),
            (int)$cycle['start_at']
        );
    }

    public function testStartWhenUnconnected()
    {
        $cycle_dao = new models\dao\Cycle();
        $request = new \Minz\Request('POST', '/cycles/starting', [
            'csrf' => (new CSRF())->generateToken(),
        ]);

        $response = self::$application->run($request);

        $this->assertSame(0, $cycle_dao->count());
        $this->assertResponse($response, 302, null, ['Location' => '/login?from=cycles%23starting']);
    }

    public function testStartFailsIfCsrfIsInvalid()
    {
        $cycle_dao = new models\dao\Cycle();
        $user_id = tests\utils\login([
            'cycles_work_weeks' => 4,
            'cycles_rest_weeks' => 1,
            'cycles_start_day' => 'monday',
        ]);
        (new CSRF())->generateToken();
        $request = new \Minz\Request('POST', '/cycles/starting', [
            'csrf' => 'not the token',
        ]);

        $response = self::$application->run($request);

        $this->assertSame(0, $cycle_dao->count());
        $this->assertResponse($response, 400);
        $variables = $response->output()->variables();
        $this->assertNotEmpty($variables['error']);
    }
}
