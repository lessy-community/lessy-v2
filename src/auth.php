<?php

namespace Lessy\controllers\auth;

use Minz\Response;
use Minz\Errors;
use Minz\Url;
use Minz\CSRF;
use Lessy\models;
use Lessy\utils;

function register($request)
{
    if (utils\currentUser()) {
        return Response::redirect('home#index');
    }

    return Response::ok('auth/register.phtml', [
        'available_locales' => utils\Locale::availableLocales(),
        'current_locale' => utils\Locale::currentLocale(),
    ]);
}

function create_user($request)
{
    if (utils\currentUser()) {
        return Response::redirect('home#index');
    }

    $user_dao = new models\dao\User();

    $username = $request->param('username');
    $email = $request->param('email');
    $password = $request->param('password');
    $locale = $request->param('locale');
    $timezone = $request->param('timezone');

    $csrf = new CSRF();
    if (!$csrf->validateToken($request->param('csrf'))) {
        return Response::badRequest('auth/register.phtml', [
            'available_locales' => utils\Locale::availableLocales(),
            'username' => $username,
            'email' => $email,
            'current_locale' => $locale,
            'timezone' => $timezone,
            'error' => _('A security verification failed, you should submit the form again.'),
        ]);
    }

    $username_exists = $user_dao->findBy(['username' => $username]) !== null;
    if ($username_exists) {
        return Response::badRequest('auth/register.phtml', [
            'available_locales' => utils\Locale::availableLocales(),
            'username' => $username,
            'email' => $email,
            'current_locale' => $locale,
            'timezone' => $timezone,
            'errors' => [
                'username' => _('This username is already used, you must choose another one.')
            ],
        ]);
    }

    try {
        $user = models\User::new($username, $email, $password, $locale, $timezone);
    } catch (Errors\ModelPropertyError $e) {
        if ($e->property() === 'username') {
            $errors = ['username' => _('This username is invalid.')];
        } elseif ($e->property() === 'email') {
            $errors = ['email' => _('This email address is invalid.')];
        } elseif ($e->property() === 'locale') {
            $errors = ['locale' => _('This language is not supported.')];
        } elseif ($e->property() === 'timezone') {
            $errors = ['timezone' => _('This timezone is not supported.')];
        } else {
            $errors = [$e->property() => $e->getMessage()];
        }

        return Response::badRequest('auth/register.phtml', [
            'available_locales' => utils\Locale::availableLocales(),
            'username' => $username,
            'email' => $email,
            'current_locale' => $locale,
            'timezone' => $timezone,
            'errors' => $errors,
        ]);
    }

    $id = $user_dao->save($user);
    if ($id) {
        $_SESSION['current_user_id'] = $id;
        return Response::redirect('cycles#preferences', ['status' => 'registered']);
    } else {
        return Response::internalServerError('auth/register.phtml', [
            'available_locales' => utils\Locale::availableLocales(),
            'username' => $username,
            'email' => $email,
            'current_locale' => $locale,
            'timezone' => $timezone,
            'error' => _('We were unable to create your account for an unknown reason. Please contact the support.'),
        ]);
    }
}

function login($request)
{
    if (utils\currentUser()) {
        return Response::redirect('home#index');
    }

    return Response::ok('auth/login.phtml');
}

function create_session($request)
{
    if (utils\currentUser()) {
        return Response::redirect('home#index');
    }

    $user_dao = new models\dao\User();

    $identifier = $request->param('identifier');
    $password = $request->param('password');

    $csrf = new CSRF();
    if (!$csrf->validateToken($request->param('csrf'))) {
        return Response::badRequest('auth/login.phtml', [
            'identifier' => $identifier,
            'error' => _('A security verification failed, you should submit the form again.'),
        ]);
    }

    if (strpos($identifier, '@') === false) {
        $user_values = $user_dao->findBy(['username' => $identifier]);
    } else {
        $user_values = $user_dao->findBy(['email' => $identifier]);
    }

    if (!$user_values) {
        return Response::badRequest('auth/login.phtml', [
            'identifier' => $identifier,
            'error' => _('We were unable to log you in, your credentials seem to be invalid.'),
        ]);
    }

    $user = new models\User($user_values);
    if ($user->verifyPassword($password)) {
        $_SESSION['current_user_id'] = $user->id;
        return Response::redirect('home#index', ['status' => 'connected']);
    } else {
        return Response::badRequest('auth/login.phtml', [
            'identifier' => $identifier,
            'error' => _('We were unable to log you in, your credentials seem to be invalid.'),
        ]);
    }
}

function delete_session($request)
{
    $csrf = new CSRF();
    if (!$csrf->validateToken($request->param('csrf'))) {
        return Response::redirect('home#index');
    }

    if (utils\currentUser()) {
        session_unset();
        models\dao\User::resetCurrentUser();
        return Response::redirect('home#index', ['status' => 'deconnected']);
    } else {
        return Response::redirect('home#index');
    }
}
