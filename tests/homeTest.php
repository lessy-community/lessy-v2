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

    public function testIndexWhenConnected()
    {
        $user_id = tests\utils\login();
        $request = new \Minz\Request('GET', '/');

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/dashboard']);
    }

    public function testDashboard()
    {
        $user_id = tests\utils\login();
        $cycle_id = self::$factories['cycles']->create([
            'user_id' => $user_id,
        ]);
        $request = new \Minz\Request('GET', '/dashboard');

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
        $variables = $response->output()->variables();
        $this->assertSame($cycle_id, $variables['current_cycle']->id);
    }

    public function testDashboardWhenNotConnected()
    {
        $request = new \Minz\Request('GET', '/dashboard');

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/login?from=home%23dashboard']);
    }

    public function testDashboardWhenNoCyclesAndOnboarded()
    {
        tests\utils\login([
            'onboarding_step' => 1,
        ]);
        $request = new \Minz\Request('GET', '/dashboard');

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/cycles/starting']);
    }

    public function testDashboardWhenNoCyclesAndNotOnboarded()
    {
        tests\utils\login([
            'onboarding_step' => 0,
        ]);
        $request = new \Minz\Request('GET', '/dashboard');

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/cycles/preferences']);
    }
}
