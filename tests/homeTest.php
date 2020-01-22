<?php

namespace Lessy\controllers\home;

use Minz\Tests\IntegrationTestCase;
use Lessy\tests;

class HomeTest extends IntegrationTestCase
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
        $request = new \Minz\Request('GET', '/');

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
        $pointer = $response->output()->pointer();
        $this->assertSame('home/index.phtml', $pointer);
    }

    public function testIndexWithStatusConnected()
    {
        $request = new \Minz\Request('GET', '/', [
            'status' => 'connected'
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
        $variables = $response->output()->variables();
        $this->assertArrayHasKey('success', $variables);
    }

    public function testIndexWithStatusDeconnected()
    {
        $request = new \Minz\Request('GET', '/', [
            'status' => 'deconnected'
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
        $variables = $response->output()->variables();
        $this->assertArrayHasKey('success', $variables);
    }

    public function testIndexWhenConnectedAndWithCycles()
    {
        $user_id = tests\utils\login();
        $cycle_id = self::$factories['cycles']->create([
            'user_id' => $user_id,
        ]);
        $request = new \Minz\Request('GET', '/');

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
        $variables = $response->output()->variables();
        $pointer = $response->output()->pointer();
        $this->assertArrayHasKey('current_cycle', $variables);
        $this->assertSame($cycle_id, $variables['current_cycle']->id);
        $this->assertSame('home/dashboard.phtml', $pointer);
    }

    public function testIndexWhenConnectedNoCyclesAndOnboarded()
    {
        tests\utils\login([
            'onboarding_step' => 1,
        ]);
        $request = new \Minz\Request('GET', '/');

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/cycles/starting']);
    }

    public function testIndexWhenConnectedNoCyclesAndNotOnboarded()
    {
        tests\utils\login([
            'onboarding_step' => 0,
        ]);
        $request = new \Minz\Request('GET', '/');

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/cycles/preferences']);
    }
}
