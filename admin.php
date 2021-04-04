<?php

/*
##############################################
##											##
##	Rotas utilizadas pelas páginas admin	##
##											##
##############################################
*/

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

//Rota para página Admin. Acessível apenas por login, com verificação de sessão logada
$app->get('/admin', function() {
	
	User::verifyLogin();
    //No caso do templater de login, o header e o footer são diferentes, estão incluídos no próprio arquivo. Utilizada classe PageAdmin, e parâmetros como array no construtor do objeto, passando header e footer como false.
    
	$page = new PageAdmin();
	
	$page->setTpl("index");
	
});

//Rota para página de login - template via get. Header e footer suprimidos.
$app->get('/admin/login', function() {
    
	$page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
	
	$page->setTpl("login");
	
});

//Rota para método de login via post, classe User
$app->post('/admin/login', function() {
	
	User::login($_POST["login"], $_POST["password"]);//Recebe e valida os dados de login
	
	//Caso haja sucesso na validação anterior, segue para a página de administração
	header("Location: /admin");
	exit;
});

//Rota para método de logout via get, inserida em botão no template de admin
$app->get('/admin/logout', function() {
	
	User::logout();
	
	header("Location: /admin/login");
	exit;
	
});

//Rota para "esqueci minha senha" da tela de login
$app->get('/admin/forgot', function(){
    
	$page = new PageAdmin([
        "header"=>false,
        "footer"=>false	
	]);
	
	$page->setTpl("forgot");
});

//Rota de envio em post do e-mail para o esqueci minha senha
$app->post('/admin/forgot', function(){
	
	$user = User::getForgot($_POST["email"]);
	
	header("Location: /admin/forgot/sent");
	exit;
});

//Rota de confirmação do e-mail enviado
$app->get("/admin/forgot/sent", function(){
	
	$page = new PageAdmin([
        "header"=>false,
        "footer"=>false	
	]);
	
	$page->setTpl("forgot-sent");
});

//Rota para página de reset da senha 
$app->get("/admin/forgot/reset", function(){
	
	$user = User::validForgotDecrypt($_GET["code"]);
	
	$page = new PageAdmin([
        "header"=>false,
        "footer"=>false	
	]);
	
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
	
});

//Método de troca de senha e inserção no banco
$app->post("/admin/forgot/reset", function(){
	
	$forgot = User::validForgotDecrypt($_POST["code"]);
	
	User::setForgotUsed($forgot["idrecovery"]);
	
	$user = new User();
	
	$user->get((int)$forgot["iduser"]);
	
	$password = User::hashPass($_POST["password"]);
	
	$user->setPassword($password);
	
	$page = new PageAdmin([
        "header"=>false,
        "footer"=>false	
	]);
	
	$page->setTpl("forgot-reset-success");
	
});

?>