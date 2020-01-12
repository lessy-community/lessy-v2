<?php

namespace Lessy\controllers\system;

use Minz\Response;
use Minz\Output;

/**
 * Initialize the database.
 *
 * @param \Minz\Request $request
 *
 * @return \Minz\Response
 */
function init($request)
{
    $schema = file_get_contents(\Minz\Configuration::$app_path . '/src/schema.sql');
    $database = \Minz\Database::get();
    $database->exec($schema);

    $output = new Output\Text("Database initialized\n");
    return new Response(200, $output);
}
