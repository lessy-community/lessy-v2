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
        'username' => \Minz\Tests\DatabaseFactory::sequence('a', function ($n) {
            return 'john-' . $n;
        }),
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
        'number' => \Minz\Tests\DatabaseFactory::sequence(),
        'start_at' => time(),
        'work_weeks' => 4,
        'rest_weeks' => 1,
        'end_at' => time() + ((5 * 7) - 1) * 86400,
    ]
);

\Minz\Tests\DatabaseFactory::addFactory(
    'tasks',
    '\Lessy\models\dao\Task',
    [
        'created_at' => time(),
        'user_id' => function () {
            $users_factory = new \Minz\Tests\DatabaseFactory('users');
            return $users_factory->create();
        },
        'label' => 'Do something, John',
        'priority' => \Minz\Tests\DatabaseFactory::sequence(),
        'planned_count' => 0,
    ]
);

include $app_path . '/tests/utils.php';
