<?php

namespace Lessy\controllers\auth;

use Minz\Tests\IntegrationTestCase;
use Minz\CSRF;

class SessionsTest extends IntegrationTestCase
{
    public function testUpdateLocale()
    {
        $_SESSION['locale'] = 'fr_FR';
        $request = new \Minz\Request('POST', '/sessions/locale', [
            'locale' => 'en_GB',
            'csrf' => (new CSRF())->generateToken(),
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/']);
        $this->assertSame('en_GB', $_SESSION['locale']);
    }

    public function testUpdateLocaleWhenLocaleIsInvalid()
    {
        $request = new \Minz\Request('POST', '/sessions/locale', [
            'locale' => 'invalid',
            'csrf' => (new CSRF())->generateToken(),
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/']);
        $this->assertArrayNotHasKey('locale', $_SESSION);
    }

    public function testUpdateLocaleWhenCsrfIsWrong()
    {
        (new CSRF())->generateToken();
        $request = new \Minz\Request('POST', '/sessions/locale', [
            'locale' => 'fr_FR',
            'csrf' => 'not the token',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/']);
        $this->assertArrayNotHasKey('locale', $_SESSION);
    }
}
