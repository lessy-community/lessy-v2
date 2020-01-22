<?php

namespace Lessy\migration_20200122_192612_AddOnboardingStepToUsers;

function migrate()
{
    $database = \Minz\Database::get();
    $column = 'onboarding_step INTEGER NOT NULL DEFAULT 0';
    $sql = "ALTER TABLE users ADD COLUMN {$column}";
    $result = $database->exec($sql);

    if ($result === false) {
        $error_info = $database->errorInfo();
        throw new \Minz\Errors\DatabaseModelError(
            "Error in SQL statement: {$error_info[2]} ({$error_info[0]})."
        );
    }

    return true;
}
