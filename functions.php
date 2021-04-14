<?php

use \Hcode\Model\User;
use \Hcode\Model\Cart;

//Função para formatar preço
function formatPrice($vlprice)
{
	
	if(!$vlprice > 0) $vlprice = 0;
	
	return number_format((float)$vlprice, 2, ",", ".");
	
}

function formatDate($date)
{
	
	return date("d/m/Y", strtotime($date));
	
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

//Função para retornar o número de produtos do carrinho
function getCartNrQtd()
{
	if(isset($_SESSION[Cart::SESSION])){
	
		$cart = Cart::getFromSession();
		
		$totals = $cart->getProductsTotals();
		
		return $totals["nrqtd"];

	} else {
		
		return "0";
		
	}
}

//Função para retornar o valor dos produtos no carrinho
function getCartVlSubTotal()
{
	
	if(isset($_SESSION[Cart::SESSION])){
		
		$cart = Cart::getFromSession();
		
		$totals = $cart->getProductsTotals();

		return formatPrice($totals["vlprice"]);
	
	} else {
		
		return "0,00";
	}
	
}
?>