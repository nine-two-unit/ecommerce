<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

//Rota para a homepage
$app->get('/', function() {
    
	$page = new Page();
	
	$page->setTpl("index");
	
});

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

//Rota para listagem de usuários dentro da administração
$app->get('/admin/users', function(){
	
	User::verifyLogin();
	
	$users = User::listAll();

	$page = new PageAdmin();
	
	$page->setTpl("users", array(
		"users"=>$users
	));
	
});

//Rota para criação de novos logins dentro da administração
$app->get('/admin/users/create', function(){
	
	User::verifyLogin();

	$page = new PageAdmin();
	
	$page->setTpl("users-create");
	
});

//Rota para deleter usuários
// Método delete fica antes do método de Update do usuário, em razão do slim ler as rotas em ordem. Como há um complemento no mesmo endereço, é necessário deixá-lo antes para que não seja ignorado pelo outro
$app->get('/admin/users/:iduser/delete', function($iduser){
	
	User::verifyLogin();
	
	$user = new User();
	
	$user->get((int)$iduser);
	
	$user->delete();
	
	header("Location: /admin/users");
	exit;
	
});

//Rota dinâmica para página de edição de usuário, id do usuário recuperada do DB vai como endereço na URL
$app->get('/admin/users/:iduser', function($iduser){ //Passando o ID de usuário na rota (boas práticas em rotas) - :iduser é recebido como variável na função ($iduser)
	
	User::verifyLogin();
	
	$user = new User();
	
	$user->get((int)$iduser);

	$page = new PageAdmin();
	
	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));
	
});

//Rota para receber post e salvar os dados de usuários novos no banco de dados
$app->post('/admin/users/create', function(){
	
	User::verifyLogin();
	
	$user = new User();
	
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
	
	$user->SetData($_POST);
	
	$user->save();
	
	header("Location: /admin/users");
	exit;
	
});

//Rota para receber post e salvar edição de usuários no banco de dados
$app->post('/admin/users/:iduser', function($iduser){
	
	User::verifyLogin();
	
	$user = new User();
	
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
	
	$user->get((int)$iduser);
	
	$user->setData($_POST);
	
	$user->update();
	
	header("Location: /admin/users");
	exit;
	
});

//Execução do Slim
$app->run();

 ?>