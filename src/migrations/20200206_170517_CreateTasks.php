<?php

namespace Lessy\migration_20200206_170517_CreateTasks;

function migrate()
{
    $database = \Minz\Database::get();

    $sql = <<<SQL
CREATE TABLE tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    created_at DATETIME NOT NULL,
    planned_at DATETIME,
    due_at DATETIME,
    finished_at DATETIME,
    label TEXT NOT NULL,
    priority INTEGER NOT NULL DEFAULT 0,
    planned_count INTEGER NOT NULL DEFAULT 0,
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
