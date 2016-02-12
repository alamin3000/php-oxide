<?php
error_reporting(E_ALL | E_NOTICE | E_STRICT);
ini_set("display_errors", "on");
require_once '../../vendor/autoload.php';

echo "<h1>start</h1>";
$fc = Oxide\Loader::bootstrap('../app/config', false);
//$fc->run();

echo "<h1>End</h1>";