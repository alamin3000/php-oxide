<?php
error_reporting(E_ALL | E_NOTICE | E_STRICT);
ini_set("display_errors", "on");
require_once '../../vendor/autoload.php';
$fc = oxide\Loader::bootstrap('../app/config', false);

$fc->run();