<?php
error_reporting(E_ALL);
ini_set("display_errors", "on");
require_once '../../vendor/autoload.php';


define("PUBLIC_ROOT", dirname(__FILE__));



$fc = oxide\Loader::bootstrap('../config', true);