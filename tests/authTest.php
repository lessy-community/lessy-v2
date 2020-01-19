<?php

namespace Lessy\controllers\auth;

use Minz\Tests\IntegrationTestCase;
use Minz\CSRF;
use Lessy\tests;
use Lessy\models;

class AuthTest extends IntegrationTestCase
{
    /**
     * @after
     */
    public function logout()
    {
        tests\utils\logout();
    }

    public function testRegister()
    {
        $request = new \Minz\Request('GET', '/register');

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
    }

    public function testRegisterWhenConnected()
    {
        tests\utils\login();
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
        tests\utils\login();
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

    public function testLogin()
    {
        $request = new \Minz\Request('GET', '/login');

        $response = self::$application->run($request);

        $this->assertResponse($response, 200);
    }

    public function testLoginWhenConnected()
    {
        tests\utils\login();
        $request = new \Minz\Request('GET', '/login');

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/']);
    }

    public function testCreateSession()
    {
        $user_dao = new models\dao\User();
        $user_id = self::$factories['users']->create([
            'username' => 'john',
            'password_hash' => password_hash('secret', PASSWORD_BCRYPT),
        ]);
        $request = new \Minz\Request('POST', '/login', [
            'csrf' => (new CSRF())->generateToken(),
            'identifier' => 'john',
            'password' => 'secret',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, [
            'Location' => '/?status=connected'
        ]);
        $this->assertSame($user_id, $_SESSION['current_user_id']);
    }

    public function testCreateSessionWhenConnected()
    {
        tests\utils\login();
        $request = new \Minz\Request('POST', '/login', [
            'csrf' => (new CSRF())->generateToken(),
            'identifier' => 'john',
            'password' => 'secret',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/']);
    }

    public function testCreateSessionWithEmail()
    {
        $user_dao = new models\dao\User();
        $user_id = self::$factories['users']->create([
            'email' => 'john@doe.com',
            'password_hash' => password_hash('secret', PASSWORD_BCRYPT),
        ]);
        $request = new \Minz\Request('POST', '/login', [
            'csrf' => (new CSRF())->generateToken(),
            'identifier' => 'john@doe.com',
            'password' => 'secret',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, [
            'Location' => '/?status=connected'
        ]);
        $this->assertSame($user_id, $_SESSION['current_user_id']);
    }

    public function testCreateSessionFailsIfCsrfIsWrong()
    {
        $user_dao = new models\dao\User();
        $user_id = self::$factories['users']->create([
            'username' => 'john',
            'password_hash' => password_hash('secret', PASSWORD_BCRYPT),
        ]);
        (new CSRF())->generateToken();
        $request = new \Minz\Request('POST', '/login', [
            'csrf' => 'not the token',
            'identifier' => 'john',
            'password' => 'secret',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 400);
        $this->assertArrayNotHasKey('current_user_id', $_SESSION);
        $variables = $response->output()->variables();
        $this->assertSame('john', $variables['identifier']);
        $this->assertNotEmpty($variables['error']);
    }

    public function testCreateSessionFailsIfUsernameDoesNotExist()
    {
        $user_dao = new models\dao\User();
        $request = new \Minz\Request('POST', '/login', [
            'csrf' => (new CSRF())->generateToken(),
            'identifier' => 'john',
            'password' => 'secret',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 400);
        $this->assertArrayNotHasKey('current_user_id', $_SESSION);
        $variables = $response->output()->variables();
        $this->assertSame('john', $variables['identifier']);
        $this->assertNotEmpty($variables['error']);
    }

    public function testCreateSessionFailsIfPasswordIsInvalid()
    {
        $user_dao = new models\dao\User();
        $user_id = self::$factories['users']->create([
            'username' => 'john',
            'password_hash' => password_hash('secret', PASSWORD_BCRYPT),
        ]);
        $request = new \Minz\Request('POST', '/login', [
            'csrf' => (new CSRF())->generateToken(),
            'identifier' => 'john',
            'password' => 'not the secret',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 400);
        $this->assertArrayNotHasKey('current_user_id', $_SESSION);
        $variables = $response->output()->variables();
        $this->assertSame('john', $variables['identifier']);
        $this->assertNotEmpty($variables['error']);
    }

    public function testDeleteSession()
    {
        tests\utils\login();
        $request = new \Minz\Request('POST', '/logout', [
            'csrf' => (new CSRF())->generateToken(),
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, [
            'Location' => '/?status=deconnected'
        ]);
        $this->assertArrayNotHasKey('current_user_id', $_SESSION);
    }

    public function testDeleteSessionWhenUnconnected()
    {
        $request = new \Minz\Request('POST', '/logout', [
            'csrf' => (new CSRF())->generateToken(),
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/']);
    }

    public function testDeleteSessionFailsIfCsrfIsWrong()
    {
        tests\utils\login();
        (new CSRF())->generateToken();
        $request = new \Minz\Request('POST', '/logout', [
            'csrf' => 'not the token',
        ]);

        $response = self::$application->run($request);

        $this->assertResponse($response, 302, null, ['Location' => '/']);
        $this->assertArrayHasKey('current_user_id', $_SESSION);
    }
}
