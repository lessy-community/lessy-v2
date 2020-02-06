<?php

namespace Lessy\models\dao;

use Lessy\models;

class Task extends \Minz\DatabaseModel
{
    public function __construct()
    {
        $properties = array_keys(models\Task::PROPERTIES);
        parent::__construct('tasks', 'id', $properties);
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

    public function listForUser($user_id)
    {
        $sql = "SELECT * FROM {$this->table_name} "
             . "WHERE user_id = :user_id ORDER BY priority";

        $statement = $this->prepare($sql);
        $result = $statement->execute([
            ':user_id' => $user_id,
        ]);
        if (!$result) {
            throw self::sqlStatementError($statement);
        }

        $result = $statement->fetchAll();
        if ($result !== false) {
            return $result;
        } else {
            throw self::sqlStatementError($statement);
        }
    }

    public function highestPriorityForUser($user_id)
    {
        $sql = "SELECT MAX(priority) FROM {$this->table_name} WHERE user_id = ?";
        $statement = $this->prepare($sql);
        $result = $statement->execute([$user_id]);
        if (!$result) {
            throw self::sqlStatementError($statement);
        }

        return intval($statement->fetchColumn());
    }
}
