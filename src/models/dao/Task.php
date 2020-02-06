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
}
