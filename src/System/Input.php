<?php

namespace JPGerber\ChaosCRUD\System;

class Input
{
    public static function validate($var)
    {
        if (is_int($var)) {
            return self::sanitizeInt(self::validateInt($var));
        } elseif (is_float($var)) {
            return self::sanitizeFloat(self::validateFloat($var));
        } elseif (filter_var($var, FILTER_VALIDATE_EMAIL)) {
            return self::sanitizeEmail($var);
        } elseif (is_array($var)) {
            foreach ($var as $key => $value) {
                $var[$key] = self::validate($value);
            }
        } else {
            return self::sanitizeGeneral($var);
        }
        return $var;
    }

    public static function validateInt($var)
    {
        return filter_var($var, FILTER_VALIDATE_INT);
    }

    public static function validateFloat($var)
    {
        return filter_var($var, FILTER_VALIDATE_FLOAT);
    }

    public static function validateEmail($var)
    {
        return filter_var($var, FILTER_VALIDATE_EMAIL);
    }

    public static function sanitizeInt($var)
    {
        return filter_var($var, FILTER_SANITIZE_NUMBER_INT);
    }

    public static function sanitizeFloat($var)
    {
        return filter_var($var, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public static function sanitizeEmail($var)
    {
        return filter_var($var, FILTER_SANITIZE_EMAIL);
    }

    public static function sanitizeGeneral($var)
    {
        return htmlentities($var, ENT_QUOTES);
    }
}
