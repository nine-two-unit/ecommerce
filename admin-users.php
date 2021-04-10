<?php

/*
##################################################
##												##
##	Rotas utilizadas pelas páginas admin/users	##
##												##
##################################################
*/

use \Hcode\PageAdmin;
use \Hcode\Model\User;

//Rota para listagem de usuários dentro da administração (READ)
$app->get('/admin/users', function(){
	
	User::verifyLogin();
	
	$users = User::listAll();
	
	/*var_dump($users);
	exit;*/
	
	$page = new PageAdmin();
	
	$page->setTpl("users", array(
		"users"=>$users
	));
	
});

//Rota para página de criação de novos logins dentro da administração
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
	
	//var_dump($user);

	$page = new PageAdmin();
	
	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));
	
});

//Rota para receber post e salvar os dados de usuários novos no banco de dados
$app->post('/admin/users/create', function(){
	
	User::verifyLogin();
	
	$user = new User();
	
	//Verificação da opção inadmin. Se for definido o valor é 1, senão é 0.
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
	
	//Método personalizado para criptografar a senha em hash 
	$user->hashPassGet($_POST["despassword"]);
	
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
	
	//Método personalizado para criptografar a senha em hash
	$user->hashPassGet($_POST["despassword"]);
	
	$user->setData($_POST);
	
	$user->update();
	
	header("Location: /admin/users");
	exit;
	
});

?>