<?php
require_once("./classes/Database.cls.php");
require_once("./config.inc.php");

$db = new Database($config['dbhost'], $config['dbname'], $config['dbuser'], $config['dbpw']);

?>
