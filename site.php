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
use \Hcode\Model\Category;

//Rota para a homepage
$app->get('/', function() {
    
	$products = Product::listAll();
	
	$page = new Page();
	
	$page->setTpl("index", [
		"products"=>Product::checkList($products)
	]);
	
});

//Rota para página das categorias
$app->get("/categories/:idcategory", function($idcategory){
	
	$category = new Category();
	
	$category->get((int)$idcategory);
	
	$page = new Page();
	
	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>Product::checkList($category->getProducts())
	]);
	
});

?>