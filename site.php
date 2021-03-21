<?php

/*
######################################################
##													##
##	Rotas utilizadas pelas páginas site (principal)	##
##													##
######################################################
*/

use \Hcode\Page;
use \Hcode\Model\Product;

//Rota para a homepage
$app->get('/', function() {
    
	$products = Product::listAll();
	
	$page = new Page();
	
	$page->setTpl("index", [
		"products"=>Product::checkList($products)
	]);
	
});


?>