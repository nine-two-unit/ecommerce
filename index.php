<?php 

/*
##################################
##								##
##	Arquivo principal do site	##
##								##
##################################
*/

session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;

$app = new Slim();

$app->config('debug', true);

require_once("site.php");
require_once("functions.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");

//Execução do Slim
$app->run();

?>