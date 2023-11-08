<?php
require_once(__DIR__ . "/../vendor/autoload.php");

$var = "<script>alert('DING')</script>";

echo validate($var);