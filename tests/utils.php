<?php

namespace Lessy\tests\utils;

function login($user_values = [])
{
    $factory = new \Minz\Tests\DatabaseFactory('users');
    $user_id = $factory->create($user_values);
    $_SESSION['current_user_id'] = $user_id;
    return $user_id;
}

function logout()
{
    session_unset();
}
