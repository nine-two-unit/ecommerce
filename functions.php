<?php

use \Hcode\Model\User;

//Função para formatar preço
function formatPrice($vlprice)
{
	
	if(!$vlprice > 0) $vlprice = 0;
	
	return number_format((float)$vlprice, 2, ",", ".");
	
}

//Função de checagem do login, utilizada pelo RainTPL
function checkLogin($inadmin = true)
{
	
	return User::checkLogin($inadmin);
	
}

//Função para retornar o nome de usuário logado
function getUserName()
{
	
	$user = User::getFromSession();
	
	return $user->getdesperson();
	
}
?>