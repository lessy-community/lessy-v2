<?php

/**
 * This file contains helper methods to be used in view files. It doesn't
 * declare a namespace on purpose.
 */

/**
 * Transform a locale to BCP47 format
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/lang
 * @see https://www.ietf.org/rfc/bcp/bcp47.txt
 *
 * @param string $locale
 *
 * @return string
 */
function localeToBCP47($locale)
{
    $splitted_locale = explode('_', $locale, 2);
    if (!$splitted_locale) {
        return $locale;
    }

    if (count($splitted_locale) === 1) {
        return $splitted_locale[0];
    }

    return $splitted_locale[0] . '-' . strtoupper($splitted_locale[1]);
}
