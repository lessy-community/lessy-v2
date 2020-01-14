<?php

namespace Lessy\controllers\home;

use Minz\Tests\IntegrationTestCase;

class HomeTest extends IntegrationTestCase
{
    public function testIndex()
    {
        $request = new \Minz\Request('GET', '/');

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
    }

    public function testIndexWithStatusRegistered()
    {
        $request = new \Minz\Request('GET', '/', [
            'status' => 'registered'
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
        $variables = $response->output()->variables();
        $this->assertArrayHasKey('success', $variables);
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
}
