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

    $csrf = new CSRF();
    if (!$csrf->validateToken($request->param('csrf'))) {
        return Response::badRequest('auth/register.phtml', [
            'available_locales' => utils\Locale::availableLocales(),
            'username' => $username,
            'email' => $email,
            'current_locale' => $locale,
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
            'errors' => [
                'username' => _('This username is already used, you must choose another one.')
            ],
        ]);
    }

    try {
        $user = models\User::new($username, $email, $password, $locale);
    } catch (Errors\ModelPropertyError $e) {
        if ($e->property() === 'username') {
            $errors = ['username' => _('This username is invalid.')];
        } elseif ($e->property() === 'email') {
            $errors = ['email' => _('This email address is invalid.')];
        } elseif ($e->property() === 'locale') {
            $errors = ['locale' => _('This language is not supported.')];
        } else {
            $errors = [$e->property() => $e->getMessage()];
        }

        return Response::badRequest('auth/register.phtml', [
            'available_locales' => utils\Locale::availableLocales(),
            'username' => $username,
            'email' => $email,
            'current_locale' => $locale,
            'errors' => $errors,
        ]);
    }

    $id = $user_dao->save($user);
    if ($id) {
        $_SESSION['current_user_id'] = $id;
        return Response::redirect('home#index', ['status' => 'registered']);
    } else {
        return Response::internalServerError('auth/register.phtml', [
            'available_locales' => utils\Locale::availableLocales(),
            'username' => $username,
            'email' => $email,
            'current_locale' => $locale,
            'error' => _('We were unable to create your account for an unknown reason. Please contact the support.'),
        ]);
    }
}
