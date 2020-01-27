<?php

namespace Lessy;

class Application
{
    /** @var \Minz\Engine **/
    private $engine;

    public function __construct()
    {
        include_once('utils/application.php');
        include_once('utils/view_helpers.php');

        // Initialize the routes
        $router = new \Minz\Router();
        $router->addRoute('get', '/', 'home#index');

        $router->addRoute('cli', '/system/init', 'system#init');
        $router->addRoute('cli', '/system/migrate', 'system#migrate');

        $router->addRoute('get', '/register', 'auth#register');
        $router->addRoute('post', '/register', 'auth#create_user');
        $router->addRoute('get', '/login', 'auth#login');
        $router->addRoute('post', '/login', 'auth#create_session');
        $router->addRoute('post', '/logout', 'auth#delete_session');
        $router->addRoute('post', '/sessions/locale', 'sessions#update_locale');

        $router->addRoute('get', '/cycles/preferences', 'cycles#preferences');
        $router->addRoute('post', '/cycles/preferences', 'cycles#update_preferences');
        $router->addRoute('get', '/cycles/starting', 'cycles#starting');
        $router->addRoute('post', '/cycles/starting', 'cycles#start');

        $this->engine = new \Minz\Engine($router);
        \Minz\Url::setRouter($router);

        // Initialize the localization
        bindtextdomain('main', utils\Locale::localesPath());
        textdomain('main');
    }

    public function run($request)
    {
        $current_user = utils\currentUser();

        // Setup correct localization
        if ($current_user) {
            date_default_timezone_set($current_user->timezone);
            $locale = $current_user->locale;
        } elseif (isset($_SESSION['locale'])) {
            $locale = $_SESSION['locale'];
        } else {
            $locale = utils\Locale::defaultLocale();
        }
        utils\Locale::setCurrentLocale($locale);

        // Initialize the default variables accessible from the views
        $status = $request->param('status');
        $success = null;
        if ($status === 'connected') {
            $success = _('You’re now connected, welcome back!');
        } elseif ($status === 'deconnected') {
            $success = _('You’re now disconnected, see you!');
        }

        \Minz\Output\View::declareDefaultVariables([
            'environment' => \Minz\Configuration::$environment,
            'javascript_configuration' => utils\javascriptConfiguration(),
            'success' => $success,
            'error' => null,
            'errors' => [],
            'current_user' => $current_user,
            'current_locale' => $locale,
        ]);

        // Run the request against the engine
        $response = $this->engine->run($request);
        $response->setContentSecurityPolicy('script-src', "'self' 'unsafe-eval'");
        return $response;
    }
}
