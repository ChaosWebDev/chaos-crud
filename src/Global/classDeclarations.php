<?php

use JPGerber\ChaosCRUD\System\Input;

function db()
{
    static $instance = null;
    if ($instance === null) {
        $instance = new JPGerber\ChaosCRUD\Network\CRUD();
    }
    return $instance;
}

function ip()
{
    static $instance = null;
    if ($instance === null) {
        $instance = new JPGerber\ChaosCRUD\Network\IP();
    }
    return $instance;
}

function validate($var)
{
    return Input::validate($var);
}
