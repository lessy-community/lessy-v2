<?php

namespace Lessy\models\dao;

use Lessy\models;

class Cycle extends \Minz\DatabaseModel
{
    public function __construct()
    {
        $properties = array_keys(models\Cycle::PROPERTIES);
        parent::__construct('cycles', 'id', $properties);
    }

    public function save($model)
    {
        if ($model->id === null) {
            $values = $model->toValues();
            $values['created_at'] = \Minz\Time::now()->getTimestamp();
            return $this->create($values);
        } else {
            $values = $model->toValues();
            $this->update($model->id, $values);
            return true;
        }
    }

    public function countForUser($user_id)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE user_id = ?;";

        $statement = $this->prepare($sql);
        $result = $statement->execute([$user_id]);
        if (!$result) {
            throw self::sqlStatementError($statement);
        }

        return intval($statement->fetchColumn());
    }
}
