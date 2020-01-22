<?php

$app_path = realpath(__DIR__ . '/..');

include $app_path . '/autoload.php';

\Minz\Configuration::load('test', $app_path);
\Minz\Environment::initialize();

// Initialize factories
\Minz\Tests\DatabaseFactory::addFactory(
    'users',
    '\Lessy\models\dao\User',
    [
        'created_at' => time(),
        'username' => 'john',
        'email' => 'john@doe.com',
        'password_hash' => password_hash('secret', PASSWORD_BCRYPT),
        'locale' => 'en_GB',
        'timezone' => 'UTC',
    ]
);

\Minz\Tests\DatabaseFactory::addFactory(
    'cycles',
    '\Lessy\models\dao\Cycle',
    [
        'created_at' => time(),
        'user_id' => function () {
            $users_factory = new \Minz\Tests\DatabaseFactory('users');
            return $users_factory->create();
        },
        'number' => 1,
        'start_at' => time(),
        'work_weeks' => 4,
        'rest_weeks' => 1,
        'end_at' => time() + ((5 * 7) - 1) * 86400,
    ]
);

include $app_path . '/tests/utils.php';
