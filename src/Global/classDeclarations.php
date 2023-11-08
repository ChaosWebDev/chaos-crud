<?php

use JPGerber\System\Input;

function db()
{
    static $instance = null;
    if ($instance === null) {
        $instance = new JPGerber\Network\CRUD();
    }
    return $instance;
}

function ip()
{
    static $instance = null;
    if ($instance === null) {
        $instance = new JPGerber\Network\IP();
    }
    return $instance;
}

function validate($var)
{
    return Input::validate($var);
}
