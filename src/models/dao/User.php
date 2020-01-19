<?php

namespace Lessy\models\dao;

use Lessy\models;

class User extends \Minz\DatabaseModel
{
    public function __construct()
    {
        $properties = array_keys(models\User::PROPERTIES);
        parent::__construct('users', 'id', $properties);
    }

    public function save($model)
    {
        if ($model->id === null) {
            $values = $model->toValues();
            $values['created_at'] = \Minz\Time::now();
            return $this->create($values);
        } else {
            $values = $model->toValues();
            $this->update($model->id, $values);
            return true;
        }
    }

    private static $current_user_instance;

    public static function currentUser()
    {
        if (!isset($_SESSION['current_user_id'])) {
            return null;
        }

        if (self::$current_user_instance !== null) {
            return self::$current_user_instance;
        }

        $user_dao = new self();
        $current_user_values = $user_dao->find($_SESSION['current_user_id']);
        if (!$current_user_values) {
            return null;
        }

        self::$current_user_instance = new \Lessy\models\User($current_user_values);
        return self::$current_user_instance;
    }
}
