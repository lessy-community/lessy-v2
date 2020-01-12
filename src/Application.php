<?php

namespace Lessy;

class Application
{
    /** @var \Minz\Engine **/
    private $engine;

    public function __construct()
    {
        // Initialize the routes
        $router = new \Minz\Router();
        $router->addRoute('get', '/', 'home#index');

        $router->addRoute('post', '/sessions/locale', 'sessions#update_locale');

        $this->engine = new \Minz\Engine($router);
        \Minz\Url::setRouter($router);

        // Initialize the default variables accessible from the views
        \Minz\Output\View::declareDefaultVariables([
            'success' => null,
            'error' => null,
            'errors' => [],
        ]);

        // Initialize the localization
        bindtextdomain('main', utils\Locale::localesPath());
        textdomain('main');
    }

    public function run($request)
    {
        if (isset($_SESSION['locale'])) {
            $locale = $_SESSION['locale'];
        } else {
            $locale = utils\Locale::defaultLocale();
        }
        utils\Locale::setCurrentLocale($locale);

        return $this->engine->run($request);
    }
}
