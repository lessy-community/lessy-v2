<?php

namespace Lessy;

class Application
{
    /** @var \Minz\Engine **/
    private $engine;

    public function __construct()
    {
        include_once('utils/application.php');

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

        $this->engine = new \Minz\Engine($router);
        \Minz\Url::setRouter($router);

        // Initialize the default variables accessible from the views
        \Minz\Output\View::declareDefaultVariables([
            'success' => null,
            'error' => null,
            'errors' => [],
            'current_user' => utils\currentUser(),
        ]);

        // Initialize the localization
        bindtextdomain('main', utils\Locale::localesPath());
        textdomain('main');
    }

    public function run($request)
    {
        $current_user = utils\currentUser();
        if ($current_user) {
            date_default_timezone_set($current_user->timezone);
            $locale = $current_user->locale;
        } elseif (isset($_SESSION['locale'])) {
            $locale = $_SESSION['locale'];
        } else {
            $locale = utils\Locale::defaultLocale();
        }
        utils\Locale::setCurrentLocale($locale);

        return $this->engine->run($request);
    }
}
