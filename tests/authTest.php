<?php

namespace Lessy\controllers\auth;

use Minz\Tests\IntegrationTestCase;
use Minz\CSRF;
use Lessy\models;

class AuthTest extends IntegrationTestCase
{
    public function testRegister()
    {
        $request = new \Minz\Request('GET', '/register');

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
    }

    public function testRegisterWhenConnected()
    {
        $user_id = self::$factories['users']->create();
        $_SESSION['current_user_id'] = $user_id;
        $request = new \Minz\Request('GET', '/register');

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/']);
    }

    public function testCreateUser()
    {
        $user_dao = new models\dao\User();
        $request = new \Minz\Request('POST', '/register', [
            'csrf' => (new CSRF())->generateToken(),
            'username' => 'john',
            'email' => 'john@doe.com',
            'password' => 'secret',
            'locale' => 'en_GB',
        ]);
        $this->assertSame(0, $user_dao->count());

        $response = self::$application->run($request);

        $this->assertSame(1, $user_dao->count());
        $this->assertResponse($response, 302, null, [
            'Location' => '/?status=registered'
        ]);
        $user = $user_dao->findBy(['username' => 'john']);
        $this->assertSame('john@doe.com', $user['email']);
        $password_algo = password_get_info($user['password_hash'])['algo'];
        $this->assertSame(PASSWORD_BCRYPT, $password_algo);
        $this->assertSame(intval($user['id']), $_SESSION['current_user_id']);
    }

    public function testCreateUserWhenConnected()
    {
        $user_id = self::$factories['users']->create();
        $_SESSION['current_user_id'] = $user_id;
        $request = new \Minz\Request('POST', '/register', [
            'csrf' => (new CSRF())->generateToken(),
            'username' => 'john',
            'email' => 'john@doe.com',
            'password' => 'secret',
            'locale' => 'en_GB',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/']);
    }

    public function testCreateUserFailsIfCsrfIsWrong()
    {
        $user_dao = new models\dao\User();
        (new CSRF())->generateToken();
        $request = new \Minz\Request('POST', '/register', [
            'csrf' => 'not the token',
            'username' => 'john',
            'email' => 'john@doe.com',
            'password' => 'secret',
            'locale' => 'en_GB',
        ]);

        $response = self::$application->run($request);

        $this->assertSame(0, $user_dao->count());
        $this->assertResponse($response, 400);
        $variables = $response->output()->variables();
        $this->assertSame('john', $variables['username']);
        $this->assertSame('john@doe.com', $variables['email']);
        $this->assertNotEmpty($variables['error']);
    }

    public function testCreateUserFailsIfUsernameExists()
    {
        $user_dao = new models\dao\User();
        self::$factories['users']->create([
            'username' => 'john',
        ]);
        $request = new \Minz\Request('POST', '/register', [
            'csrf' => (new CSRF())->generateToken(),
            'username' => 'john',
            'email' => 'john@doe.com',
            'password' => 'secret',
            'locale' => 'en_GB',
        ]);
        $this->assertSame(1, $user_dao->count());

        $response = self::$application->run($request);

        $this->assertSame(1, $user_dao->count());
        $this->assertResponse($response, 400);
        $variables = $response->output()->variables();
        $this->assertSame('john', $variables['username']);
        $this->assertSame('john@doe.com', $variables['email']);
        $this->assertArrayHasKey('username', $variables['errors']);
    }

    public function testCreateUserFailsIfUsernameIsInvalid()
    {
        $user_dao = new models\dao\User();
        $request = new \Minz\Request('POST', '/register', [
            'csrf' => (new CSRF())->generateToken(),
            'username' => 'john doe', // spaces are not allowed
            'email' => 'john@doe.com',
            'password' => 'secret',
            'locale' => 'en_GB',
        ]);

        $response = self::$application->run($request);

        $this->assertSame(0, $user_dao->count());
        $this->assertResponse($response, 400);
        $variables = $response->output()->variables();
        $this->assertSame('john doe', $variables['username']);
        $this->assertSame('john@doe.com', $variables['email']);
        $this->assertArrayHasKey('username', $variables['errors']);
    }

    public function testCreateUserFailsIfEmailIsInvalid()
    {
        $user_dao = new models\dao\User();
        $request = new \Minz\Request('POST', '/register', [
            'csrf' => (new CSRF())->generateToken(),
            'username' => 'john',
            'email' => 'john-doe.com', // missing at (@)
            'password' => 'secret',
            'locale' => 'en_GB',
        ]);

        $response = self::$application->run($request);

        $this->assertSame(0, $user_dao->count());
        $this->assertResponse($response, 400);
        $variables = $response->output()->variables();
        $this->assertSame('john', $variables['username']);
        $this->assertSame('john-doe.com', $variables['email']);
        $this->assertArrayHasKey('email', $variables['errors']);
    }

    public function testCreateUserFailsIfLocaleIsInvalid()
    {
        $user_dao = new models\dao\User();
        $request = new \Minz\Request('POST', '/register', [
            'csrf' => (new CSRF())->generateToken(),
            'username' => 'john',
            'email' => 'john@doe.com',
            'password' => 'secret',
            'locale' => 'invalid',
        ]);

        $response = self::$application->run($request);

        $this->assertSame(0, $user_dao->count());
        $this->assertResponse($response, 400);
        $variables = $response->output()->variables();
        $this->assertSame('john', $variables['username']);
        $this->assertSame('john@doe.com', $variables['email']);
        $this->assertArrayHasKey('locale', $variables['errors']);
    }
}
