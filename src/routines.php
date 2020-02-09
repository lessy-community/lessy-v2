<?php

namespace Lessy\controllers\routines;

use Minz\Response;

function init($request)
{
    return Response::ok('routines/init.phtml');
}
