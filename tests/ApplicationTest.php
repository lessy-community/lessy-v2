<?php

namespace Lessy\controllers\home;

use Minz\Tests\IntegrationTestCase;

class ApplicationTest extends IntegrationTestCase
{
    public function testSetCurrentLocale()
    {
        $request = new \Minz\Request('GET', '/');

        self::$application->run($request);

        $this->assertSame('en_GB.UTF8', setlocale(LC_ALL, 0));
    }

    public function testSetCurrentLocaleWithSessionLocale()
    {
        $_SESSION['locale'] = 'fr_FR';
        $request = new \Minz\Request('GET', '/');

        self::$application->run($request);

        $this->assertSame('fr_FR.UTF8', setlocale(LC_ALL, 0));
    }

    public function testSetCurrentLocaleWithCurrentUser()
    {
        $user_id = self::$factories['users']->create([
            'locale' => 'fr_FR',
        ]);
        $_SESSION['current_user_id'] = $user_id;
        $request = new \Minz\Request('GET', '/');

        self::$application->run($request);

        $this->assertSame('fr_FR.UTF8', setlocale(LC_ALL, 0));
    }

    public function testStatusConnected()
    {
        $_SESSION['locale'] = 'en_GB';
        $request = new \Minz\Request('GET', '/', [
            'status' => 'connected',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
        $this->assertStringContainsString(
            'You’re now connected, welcome back!',
            $response->render()
        );
    }

    public function testStatusDeconnected()
    {
        $_SESSION['locale'] = 'en_GB';
        $request = new \Minz\Request('GET', '/', [
            'status' => 'deconnected'
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
        $this->assertStringContainsString(
            'You’re now disconnected, see you!',
            $response->render()
        );
    }
}
