<?php
require_once("./includes/common.inc.php");
require_once("./classes/Router.cls.php");

$router = new Router($config['base_path'] . '/api');

function sController($req){
  print_r($req['params']);
  print_r($req['body']);
}

$router->get('/pages', "sController");
$router->post('/pages', "sController");
$router->put('/pages', "sController");
$router->delete('/pages', "sController");
?>
