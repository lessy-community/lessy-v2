<?php

namespace Lessy\controllers\sessions;

use Minz\CSRF;
use Minz\Response;
use Lessy\utils;

function update_locale($request)
{
    $csrf = new CSRF();
    if (!$csrf->validateToken($request->param('csrf'))) {
        return Response::redirect('home#index');
    }

    $locale = $request->param('locale');
    $available_locales = utils\Locale::availableLocales();
    if (isset($available_locales[$locale])) {
        $_SESSION['locale'] = $locale;
    } else {
        \Minz\Log::warning(
            "[sessions#update_locale] Tried to set invalid `{$locale}` locale."
        );
    }
    return Response::redirect('home#index');
}
