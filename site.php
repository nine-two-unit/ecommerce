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
use \Hcode\Model\Cart;

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
	

	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;
	
	$category = new Category();
	
	$category->get((int)$idcategory);
	
	//Paginação
	$pagination = $category->getProductsPage($page);
	
	$pages = [];
	
	for ($i=1; $i <= $pagination["pages"]; $i++){
		array_push($pages, [
			"link"=>"/categories/" .$category->getidcategory()."?page=" .$i,
			"page"=>$i
		]);
	}
	
	$page = new Page();
	

	
	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>$pagination["data"],
		"pages"=>$pages
	]);
	
});

//Rota para páginas dos detalhes dos produtos
$app->get("/products/:desurl", function($desurl){
	
	$product = new Product();
	
	$product->getFromURL($desurl);
	
	$page = new Page();
	
	$page->setTpl("product-detail", [
		"product"=>$product->getValues(),
		"categories"=>$product->getCategories()
	]);
	
	//var_dump($page);
	exit;
	
});

//Rota para o carrinho de compras
$app->get("/cart", function(){
	
	$cart = Cart::getFromSession();
	
	$page = new Page();
	
	$page->setTpl("cart", [
		"cart"=>$cart->getValues(),
		"products"=>$cart->getProducts(),
		"error"=>Cart::getMsgError()
	]);
	
});

//Rota para adicionar produtos no carrinho de compras
$app->get("/cart/:idproduct/add", function($idproduct){
	
	$product = new Product();
	
	$product->get((int)$idproduct);
	
	$cart = Cart::getFromSession();
	
	$qtd = (isset($_GET["qtd"])) ? (int)$_GET["qtd"] : 1;
	
	for ($i = 0; $i < $qtd; $i++){
		
		$cart->addProduct($product);
		
	}
	
	header("Location: /cart");
	exit;
	
});

//Rota para remover um produto do carrinho de compras
$app->get("/cart/:idproduct/minus", function($idproduct){
	
	$product = new Product();
	
	$product->get((int)$idproduct);
	
	$cart = Cart::getFromSession();
	
	$cart->removeProduct($product);
	
	header("Location: /cart");
	exit;
	
});

//Rota para remover todos produtos do carrinho de compras
$app->get("/cart/:idproduct/remove", function($idproduct){
	
	$product = new Product();
	
	$product->get((int)$idproduct);
	
	$cart = Cart::getFromSession();
	
	$cart->removeProduct($product, true);
	
	header("Location: /cart");
	exit;
	
});

//Rota para total dos itens no carrinho e cálculo de frete
$app->post("/cart/freight", function(){
	
	$cart = Cart::getFromSession();
	
	$cart->setFreight($_POST["zipcode"]);
	
	header("Location: /cart");
	exit;
	
});

?>