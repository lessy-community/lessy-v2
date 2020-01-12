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
