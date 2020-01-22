<?php

namespace Lessy\models;

use Lessy\utils;

/**
 * @author Marien Fressinaud <dev@marienfressinaud.fr>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html AGPL
 */
class Cycle extends \Minz\Model
{
    public const PROPERTIES = [
        'id' => 'integer',

        'created_at' => 'datetime',

        'user_id' => [
            'type' => 'integer',
            'required' => true,
        ],

        'number' => [
            'type' => 'integer',
            'required' => true,
        ],

        'start_at' => [
            'type' => 'datetime',
            'required' => true,
        ],

        'end_at' => [
            'type' => 'datetime',
            'required' => true,
        ],

        'work_weeks' => [
            'type' => 'integer',
            'required' => true,
        ],

        'rest_weeks' => [
            'type' => 'integer',
            'required' => true,
        ],
    ];

    /**
     * @param integer $user_id
     * @param \DateTime $start_at
     * @param integer $number
     * @param integer $work_weeks
     * @param integer $rest_weeks
     *
     * @throws \Minz\Error\ModelPropertyError if one of the value is invalid
     *
     * @return \Lessy\models\Cycle
     */
    public static function new($user_id, $number, $start_at, $work_weeks, $rest_weeks)
    {
        $days_interval = ($work_weeks + $rest_weeks) * 7 - 1;
        $end_at = clone $start_at;
        $end_at->modify("+{$days_interval} days");

        return new self([
            'user_id' => $user_id,
            'number' => $number,
            'start_at' => $start_at->getTimestamp(),
            'work_weeks' => $work_weeks,
            'rest_weeks' => $rest_weeks,
            'end_at' => $end_at->getTimestamp(),
        ]);
    }

    /**
     * @param \Lessy\models\User $user
     *
     * @return \Lessy\models\Cycle
     */
    public static function newForUser($user)
    {
        $cycle_dao = new dao\Cycle();
        $number = $cycle_dao->countForUser($user->id) + 1;
        $running_cycle = $cycle_dao->findRunningForUser($user->id);

        if ($running_cycle) {
            $start_at = new \DateTime();
            $start_at->setTimestamp($running_cycle['end_at']);
            $start_at->modify('+1 day');
        } else {
            $start_at = \Minz\Time::now();
        }

        if (strcasecmp($start_at->format('l'), $user->cycles_start_day) == 0) {
            $start_at->modify('today');
        } else {
            $start_at->modify('previous ' . $user->cycles_start_day);
        }

        $work_weeks = $user->cycles_work_weeks;
        $rest_weeks = $user->cycles_rest_weeks;

        return self::new($user->id, $number, $start_at, $work_weeks, $rest_weeks);
    }

    /**
     * Initialize a Cycle from values (usually from database).
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
}
