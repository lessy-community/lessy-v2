<?php

namespace Lessy\migration_20200119_191746_AddCyclesPreferencesToUser;

function migrate()
{
    $database = \Minz\Database::get();
    $columns = [
        'cycles_work_weeks INTEGER NOT NULL DEFAULT 4',
        'cycles_rest_weeks INTEGER NOT NULL DEFAULT 1',
        'cycles_start_day TEXT NOT NULL DEFAULT "monday"',
    ];

    $database->beginTransaction();
    foreach ($columns as $column) {
        $sql = "ALTER TABLE users ADD COLUMN {$column}";
        $result = $database->exec($sql);

        if ($result === false) {
            $database->rollBack();
            $error_info = $database->errorInfo();
            throw new \Minz\Errors\DatabaseModelError(
                "Error in SQL statement: {$error_info[2]} ({$error_info[0]})."
            );
        }
    }
    $database->commit();

    return true;
}
