<?php

/*
######################################################
##													##
##	Rotas utilizadas pelas páginas admin/categories	##
##													##
######################################################
*/

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

//Rota para página de categorias no admin
$app->get("/admin/categories", function(){
	
	User::verifyLogin();
	
	$search = (isset($_GET["search"])) ? $_GET["search"] : "";
	
	$page = (isset($_GET["page"])) ? (int)$_GET["page"] : 1;
	
	if($search != ""){
		
		$pagination = Category::getPageSearch($search, $page);
		
	} else {
		
		$pagination = Category::getPage($page);
		
	}

	$pages = [];
	
	for ($x = 0; $x < $pagination["pages"]; $x++)
	{
		
		array_push($pages, [
			"href"=>"/admin/categories?".http_build_query([
				"page"=>$x+1,
				"search"=>$search
			]),
			"text"=>$x+1
		]);
		
	}	
	//$categories = Category::listAll();
	
	$page = new PageAdmin();
	
	$page->setTpl("categories", [
		"categories"=>$pagination["data"],
		"search"=>$search,
		"pages"=>$pages
	]);
	
});

//Rota para a página de cadastro de categoria
$app->get("/admin/categories/create", function(){
	
	User::verifyLogin();
	
	$page = new PageAdmin();
	
	$page->setTpl("categories-create");
	
});

//Método de inserção de nova categoria no banco
$app->post("/admin/categories/create", function(){
	
	User::verifyLogin();
	
	$category = new Category();
	
	$category->setData($_POST);

	$category->save();
	
	header("Location: /admin/categories");
	
	exit;
	
});

//Método para exclusão de categoria
$app->get("/admin/categories/:idcategory/delete", function($idcategory){
	
	User::verifyLogin();
	
	$category = new Category();
	
	$category->get((int)$idcategory);
	
	$category->delete();
	
	header("Location: /admin/categories");
	
	exit;
});

//Rota para página de edição das categorias
$app->get("/admin/categories/:idcategory", function($idcategory){
	
	User::verifyLogin();

	$category = new Category();
	
	$category->get((int)$idcategory);
	
	$page = new PageAdmin();
	
	$page->setTpl("categories-update", [
		"category"=>$category->getValues()
	]);

});

//Método para edição das categorias
$app->post("/admin/categories/:idcategory", function($idcategory){
	
	User::verifyLogin();

	$category = new Category();
	
	$category->get((int)$idcategory);
	
	$category->setData($_POST);
	
	$category->save();
	
	header("Location: /admin/categories");
	
	exit;

});


//Rota para página de relação entre categorias e produtos
$app->get("/admin/categories/:idcategory/products", function($idcategory){
	
	User::verifyLogin();
	
	$category = new Category();
	
	$category->get((int)$idcategory);
	
	$page = new PageAdmin();
	
	$page->setTpl("categories-products", [
		"category"=>$category->getValues(),
		"productsRelated"=>$category->getProducts(),
		"productsNotRelated"=>$category->getProducts(false)
	]);
	
});

//Rota para execução de método para adicionar produto à categoria
$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){
	
	User::verifyLogin();
	
	$category = new Category();
	
	$category->get((int)$idcategory);
	
	$product = new Product();
	
	$product->get((int)$idproduct);
	
	$category->addProduct($product);
	
	header("Location: /admin/categories/".$idcategory."/products");
	exit;
	
});

//Rota para execução de método para remoção de produto à categoria
$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){
	
	User::verifyLogin();
	
	$category = new Category();
	
	$category->get((int)$idcategory);
	
	$product = new Product();
	
	$product->get((int)$idproduct);
	
	$category->removeProduct($product);
	
	header("Location: /admin/categories/".$idcategory."/products");
	exit;
	
});

?>