<?php

namespace Lessy\utils;

function currentUser()
{
    return \Lessy\models\dao\User::currentUser();
}
