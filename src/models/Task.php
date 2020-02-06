<?php

namespace Lessy\models;

use Lessy\utils;

/**
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Task extends \Minz\Model
{
    public const PROPERTIES = [
        'id' => 'integer',

        'created_at' => 'datetime',

        'planned_at' => 'datetime',

        'due_at' => 'datetime',

        'finished_at' => 'datetime',

        'user_id' => [
            'type' => 'integer',
            'required' => true,
        ],

        'label' => [
            'type' => 'string',
            'required' => true,
            'validator' => '\Lessy\models\Task::validateLabel',
        ],

        'priority' => [
            'type' => 'integer',
            'required' => true,
        ],

        'planned_count' => [
            'type' => 'integer',
            'required' => true,
        ],
    ];

    /**
     * @param integer $user_id
     * @param string $label
     *
     * @throws \Minz\Error\ModelPropertyError if one of the value is invalid
     *
     * @return \Lessy\models\Task
     */
    public static function new($user_id, $label)
    {
        return new self([
            'user_id' => $user_id,
            'label' => $label,
            'priority' => 0,
            'planned_count' => 0,
        ]);
    }

    /**
     * Initialize a Task from values (usually from database).
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
     * Initialize a list of Tasks from values (usually from database).
     *
     * @param array $array_of_values
     *
     * @throws \Minz\Error\ModelPropertyError if one of the value is invalid
     *
     * @return Lessy\models\Task[]
     */
    public static function daoToTasks($array_of_values)
    {
        $tasks = [];
        foreach ($array_of_values as $values) {
            $tasks[] = new self($values);
        }
        return $tasks;
    }

    public static function validateLabel($label)
    {
        return strlen($label) > 0;
    }
}
