<?php

namespace Lessy\models;

use Lessy\utils;

/**
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class User extends \Minz\Model
{
    public const PROPERTIES = [
        'id' => 'integer',

        'created_at' => 'datetime',

        'username' => [
            'type' => 'string',
            'required' => true,
            'validator' => '\Lessy\models\User::validateUsername',
        ],

        'email' => [
            'type' => 'string',
            'required' => true,
            'validator' => '\Lessy\models\User::validateEmail',
        ],

        'password_hash' => [
            'type' => 'string',
            'required' => true,
            'validator' => '\Lessy\models\User::validatePasswordHash',
        ],

        'locale' => [
            'type' => 'string',
            'required' => true,
            'validator' => '\Lessy\models\User::validateLocale',
        ],

        'timezone' => [
            'type' => 'string',
            'required' => true,
            'validator' => '\Lessy\models\User::validateTimezone',
        ],

        'cycles_work_weeks' => [
            'type' => 'integer',
            'required' => true,
            'validator' => '\Lessy\models\User::validateCyclesWorkWeeks',
        ],

        'cycles_rest_weeks' => [
            'type' => 'integer',
            'required' => true,
            'validator' => '\Lessy\models\User::validateCyclesRestWeeks',
        ],

        'cycles_start_day' => [
            'type' => 'string',
            'required' => true,
            'validator' => '\Lessy\models\User::validateCyclesStartDay',
        ],
    ];

    /**
     * @param string $username
     * @param string $email
     * @param string $password_hash
     * @param string $locale
     * @param string $timezone
     *
     * @throws \Minz\Error\ModelPropertyError if one of the value is invalid
     */
    public static function new($username, $email, $password, $locale, $timezone)
    {
        return new self([
            'username' => $username,
            'email' => self::punyencodeEmail($email),
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'locale' => $locale,
            'timezone' => $timezone,

            'cycles_work_weeks' => 4,
            'cycles_rest_weeks' => 1,
            'cycles_start_day' => 'monday',
        ]);
    }

    /**
     * Initialize a User from values (usually from database).
     *
     * @param array $values
     *
     * @throws \Minz\Error\ModelPropertyError if one of the value is invalid
     */
    public function __construct($values)
    {
        parent::__construct(self::PROPERTIES);
        $this->fromValues($values);
    }

    /**
     * Compare a password to the stored hash.
     *
     * @param string $password
     *
     * @return boolean Return true if the password matches the hash, else false
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password_hash);
    }

    public static function punyencodeEmail($email)
    {
        $at_position = strrpos($email, '@');

        if ($at_position !== false && function_exists('idn_to_ascii')) {
            $domain = substr($email, $at_position + 1);

            if (defined('INTL_IDNA_VARIANT_UTS46')) {
                $domain = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            } elseif (defined('INTL_IDNA_VARIANT_2003')) {
                $domain = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_2003);
            } else {
                $domain = idn_to_ascii($domain);
            }

            if ($domain !== false) {
                $email = substr($email, 0, $at_position + 1) . $domain;
            }
        }

        return $email;
    }

    public static function validateUsername($username)
    {
        return preg_match('/^[0-9a-zA-Z_\-]{1,}$/', $username) === 1;
    }

    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePasswordHash($password_hash)
    {
        $infos = password_get_info($password_hash);
        return $infos['algo'] === PASSWORD_BCRYPT;
    }

    public static function validateLocale($locale)
    {
        $available_locales = utils\Locale::availableLocales();
        return isset($available_locales[$locale]);
    }

    public static function validateTimezone($timezone)
    {
        $previous_timezone = date_default_timezone_get();
        $result = @date_default_timezone_set($timezone);
        date_default_timezone_set($previous_timezone);
        return $result;
    }

    public static function validateCyclesWorkWeeks($number_weeks)
    {
        return $number_weeks >= 3 && $number_weeks <= 7;
    }

    public static function validateCyclesRestWeeks($number_weeks)
    {
        return $number_weeks >= 1 && $number_weeks <= 2;
    }

    public static function validateCyclesStartDay($day)
    {
        return $day === 'monday' || $day === 'sunday';
    }
}
