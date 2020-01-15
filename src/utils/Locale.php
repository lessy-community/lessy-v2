<?php

namespace Lessy\utils;

class Locale
{
    public static function localesPath()
    {
        return \Minz\Configuration::$app_path . '/locales';
    }

    public static function defaultLocale()
    {
        return 'en_GB';
    }

    public static function defaultLocaleName()
    {
        return 'English';
    }

    public static function currentLocale()
    {
        return substr(setlocale(LC_ALL, 0), 0, -5);
    }

    public static function setCurrentLocale($locale)
    {
        return setlocale(LC_ALL, $locale . '.UTF8');
    }

    public static function availableLocales()
    {
        $locales = [
            self::defaultLocale() => self::defaultLocaleName(),
        ];
        $locales_path = self::localesPath();
        foreach (scandir($locales_path) as $locale_dir) {
            if ($locale_dir[0] === '.') {
                continue;
            }
            $locale_name_path = $locales_path . '/' . $locale_dir . '/name.txt';
            $locale_name = trim(file_get_contents($locale_name_path));
            $locales[$locale_dir] = $locale_name;
        }
        return $locales;
    }
}
