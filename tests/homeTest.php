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
}
