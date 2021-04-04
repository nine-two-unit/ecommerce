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
use \Hcode\Model\Address;
use \Hcode\Model\User;


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
	
	//var_dump($cart->productsOnCart);
	
	//var_dump($cart->getValues());

	//exit;
	
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
	
	//$cart->clearFreight();
	
	header("Location: /cart");
	exit;
	
});

//Rota para total dos itens no carrinho e cálculo de frete
$app->post("/cart/freight", function(){
	
	$cart = Cart::getFromSession();
	
	$cart->setFreight($_POST["zipcode"]);
	
	header("Location: /cart");
	//var_dump($_POST);
	exit;
	
});

//Rota para finalização de compras
$app->get("/checkout", function(){
	
	User::verifyLogin(false);
	
	$cart = Cart::getFromSession();
	
	$user = User::getFromSession();
	
	$address = new Address();
	
	$page = new Page();
	
	$page->setTpl("checkout", [
		"cart"=>$cart->getValues(),
		"address"=>$address->getValues()
	]);
	
});

//Rota para login do usuário
$app->get("/login", function(){
	
	$page = new Page();
	
	$page->setTpl("login", [
		"error"=>User::getError(),
		"errorRegister"=>User::getErrorRegister(),
		"registerValues"=>(isset($_SESSION["registerValues"])) ? $_SESSION["registerValues"] : ["name"=>"", "email"=>"", "phone"=>""]
	]);
	
});

$app->post("/login", function(){
	
	try {
		
		User::login($_POST["login"],$_POST["password"]);
	
	} catch(Exception $e){
		
		User::setError($e->getMessage());
	} 

	header("Location: /checkout");
	exit;
	
});

$app->get("/logout", function(){
	
	User::logout();
	
	header("Location: /login");
	exit;
	
});

//Rota para cadastro de usuário
$app->post("/register", function(){
	
	$_SESSION["registerValues"] = $_POST;
	
	//Verifica campos em branco
	if(!isset($_POST["name"]) || $_POST["name"] == "") {
		
		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /login");
		exit;
		
	}
	
	if(!isset($_POST["email"]) || $_POST["email"] == "") {
		
		User::setErrorRegister("Preencha o seu e-mail.");
		header("Location: /login");
		exit;
		
	}
	
	if(!isset($_POST["password"]) || $_POST["password"] == "") {
		
		User::setErrorRegister("Preencha a senha.");
		header("Location: /login");
		exit;
		
	}
	
	//Checa se o usuário já existe no banco
	if(User::checkLoginExists($_POST["email"]) === true){
		
		User::setErrorRegister("Este endereço de e-mail já está sendo utilizado por outro usuário.");
		header("Location: /login");
		exit;
		
	}
	
	$user = new User();
	
	$user->setData([
		"inadmin"=>0,
		"deslogin"=>$_POST["email"],
		"desperson"=>$_POST["name"],
		"desemail"=>$_POST["email"],
		"despassword"=>$_POST["password"],
		"nrphone"=>$_POST["phone"]
	]);
	
	$user->save();
	
	$_SESSION["registerValues"] = NULL;
	
	User::login($_POST["email"], $_POST["password"]);
	
	header("Location: /checkout");
	exit;
});
?>