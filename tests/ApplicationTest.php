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
}
