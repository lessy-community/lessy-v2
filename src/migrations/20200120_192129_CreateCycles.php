<?php

namespace Lessy\migration_20200120_192129_CreateCycles;

/**
 * @return boolean true if the migration was successful, false otherwise
 */
function migrate()
{
    $database = \Minz\Database::get();

    $sql = <<<SQL
CREATE TABLE cycles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id integer NOT NULL,
    created_at datetime NOT NULL,
    number INTEGER NOT NULL,
    start_at datetime NOT NULL,
    work_weeks INTEGER NOT NULL,
    rest_weeks INTEGER NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
SQL;

    $result = $database->exec($sql);
    if ($result === false) {
        $error_info = $database->errorInfo();
        throw new \Minz\Errors\DatabaseModelError(
            "Error in SQL statement: {$error_info[2]} ({$error_info[0]})."
        );
    }

    return true;
}
