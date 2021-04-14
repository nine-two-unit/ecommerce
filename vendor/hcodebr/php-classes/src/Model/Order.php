<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Model\Cart;

class Order extends Model {
	
	const SUCCESS = "Order-Success";
	const ERROR = "Order-Error";
	
	//Método para salvar pedido
	public function save()
	{
		$sql = new Sql();
		
		$results = $sql->select("CALL sp_orders_save(:idorder, :idcart, :iduser, :idstatus, :idaddress, :vltotal)", [
			":idorder"=>$this->getidorder(),
			":idcart"=>$this->getidcart(),
			":iduser"=>$this->getiduser(),
			":idstatus"=>$this->getidstatus(),
			":idaddress"=>$this->getidaddress(),
			":vltotal"=>$this->getvltotal()
		]);
		
		if (count($results) > 0){
			$this->setData($results[0]);
			
			//Seta o campo isopened com o valor de 0 (false)
			$sql->query("UPDATE tb_carts SET isopened = 0 WHERE idcart = :idcart", [
				":idcart"=>$this->getidcart()
			]);
		}
		
	}
	
	//Método para consultar pedido
	public function get($idorder)
	{
		
		$sql = new Sql();
		
		$results = $sql->select("
			SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus)
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e USING(idaddress)
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.idorder = :idorder
		", [
			":idorder"=>$idorder
		]);
		
		if (count($results) > 0){
			$this->setData($results[0]);			
		}
		
	}
	
	//Método para listar todos os pedidos
	public static function listAll()
	{
		
		$sql = new Sql();
		
		return $sql->select("
			SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus)
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e USING(idaddress)
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			ORDER BY a.dtregister DESC
		");
		
	}
	
	//Método para deletar pedidos
	public function delete()
	{
		
		$sql = new Sql();
		
		$sql->query("DELETE FROM tb_orders WHERE idorder = :idorder", [
			":idorder"=>$this->getidorder()		
		]);
		
	}
	
	public function getCart():Cart
	{
		
		$cart = new Cart();
		
		$cart->get((int)$this->getidcart());
		
		return $cart;
		
	}
	
	//Métodos de tratamento de erros	
	public static function setError($msg)
	{
		
		$_SESSION[Order::ERROR] = $msg;
		
	}
	
	public static function getError()
	{
		
		$msg = (isset($_SESSION[Order::ERROR]) && $_SESSION[Order::ERROR]) ? $_SESSION[Order::ERROR] : "";
		
		Order::clearError();
		
		return $msg;
		
	}
	
	public static function clearError()
	{
		
		$_SESSION[Order::ERROR] = NULL;
		
	}

	//Métodos de tratamento de mensagens
	
	public static function setSuccess($msg)
	{
		
		$_SESSION[Order::SUCCESS] = $msg;
		
	}
	
	public static function getSuccess()
	{
		
		$msg = (isset($_SESSION[Order::SUCCESS]) && $_SESSION[Order::SUCCESS]) ? $_SESSION[Order::SUCCESS] : "";
		
		Order::clearSuccess();
		
		return $msg;
		
	}
	
	public static function clearSuccess()
	{
		
		$_SESSION[Order::SUCCESS] = NULL;
		
	}	
}

?>