<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;


$app->get("/admin/orders/:idorder/status", function($idorder){
	
	User::verifyLogin();
	
	$order = new Order();
	
	$order->get((int)$idorder);
	
	$page = new PageAdmin();
	
	$page->SetTpl("order-status", [
		"order"=>$order->getValues(),
		"status"=>OrderStatus::listAll(),
		"msgSuccess"=>Order::getSuccess(),
		"msgError"=>Order::getError()
	]);
	
});

$app->post("/admin/orders/:idorder/status", function($idorder){

	User::verifyLogin();
	
	if(!isset($_POST["idstatus"]) || !(int)$_POST["idstatus"] > 0){
		Order::setError("Informe o status atual.");
		header("Location: /admin/orders/".$idorder."/status");
		exit;
	}
	
	$order = new Order();
	
	$order->get((int)$idorder);
	
	$order->setidstatus((int)$_POST["idstatus"]);
	
	$order->save();
	
	Order::setSuccess("Status atualizado.");
	
	header("Location: /admin/orders/".$idorder."/status");
	exit;

});

//Rota para deletar pedidos
$app->get("/admin/orders/:idorder/delete", function($idorder){
	
	User::verifyLogin();
	
	$order = new Order();
	
	$order->get((int)$idorder);
	
	$order->delete();
	
	header("Location: /admin/orders");
	exit;
	
});

//Rota para detalhes do pedidos
$app->get("/admin/orders/:idorder", function($idorder){
	
	User::verifyLogin();
	
	$order = new Order();
	
	$order->get((int)$idorder);
	
	$cart = $order->getCart();
	
	/*
	$order->getValues();
	$cart->getValues();
	$products = $cart->getProducts();
	
	
	var_dump($order);
	echo "<br><br>";
	var_dump($cart);
	echo "<br><br>";
	var_dump($products);
	exit;
	*/
	
	$page = new PageAdmin();
	
	$page->SetTpl("order", [
		"order"=>$order->getValues(),
		"cart"=>$cart->getValues(),
		"products"=>$cart->getProducts()
	]);
	
	
	
});


//Rota para listagem de pedidos
$app->get("/admin/orders", function(){
	
	User::verifyLogin();
	
	$page = new PageAdmin();
	
	$page->SetTpl("orders", [
		"orders"=>Order::listAll()	
	]);
	
});

?>