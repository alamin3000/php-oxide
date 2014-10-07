<?php
error_reporting(E_ALL);
ini_set("display_errors", "on");
require_once '../../vendor/autoload.php';


define("PUBLIC_ROOT", dirname(__FILE__));
define("APPLICATION_DIR", realpath('../application'));
define("MODULE_HOME_DIR", APPLICATION_DIR . '/module/home');

$fc = oxide\Loader::bootstrap(APPLICATION_DIR, true);