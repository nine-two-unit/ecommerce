<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model {
	
	//Funciona como ID do carrinho
	const SESSION = "Cart";
	
	//Método para verificar o status do carrinho
	public static function getFromSession()
	{
		
		$cart = new Cart();
		
		//Verifica se o carrinho já está criado e se o idcart é maior do que zero (o cast para int vira zero se a variável estiver vazia)
		if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]["idcart"] > 0){
			
			//Se o carrinho existe, o mesmo é carregado
			$cart->get((int)$_SESSION[Cart::SESSION]["idcart"]);
			
		} else {
			
			//Se não existe, é feita a tentativa de carregar o do sessionid
			$cart->getFromSessionID();
			
			//Se o carrinho não existe, então ele é criado com o session_id
			if (!(int)$cart->getidcart() > 0){
				
				$data = [
					"dessessionid"=>session_id()
				];
				
				//Verifica se há um usuário logado e retorna o ID do usuário
				if(User::checkLogin(false)){
					
					$user = User::getFromSession();
					
					$data["iduser"] = $user->getiduser();
					
				}
				
				$cart->setData($data);
				
				$cart->save();
				
				$cart->setToSession();
				
				
			}
			
			
		}
		
		//Retorna o carrinho se ele for encontrado
		return $cart;
	
	}

	//Método para atribuir o carrinho novo na sessão
	public function setToSession()
	{
		
		$_SESSION[Cart::SESSION] = $this->getValues();
		
	}

	//Método  sendo recuperado atráves do sessionid
	public function getFromSessionID()
	{
		
		$sql = new Sql();
		
		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
			":dessessionid"=>session_id()		
		]);
		
		if (count($results) > 0){
			
			$this->setData($results[0]);
		
		}

		
	}

	//Método para carregar o carrinho
	public function get(int $idcart)
	{
		
		$sql = new Sql();
		
		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
			":idcart"=>$idcart		
		]);
		
		if (count($results) > 0){
			
			$this->setData($results[0]);
		
		}
		
	}
	
	//Método para salvar os dados do carrinho no banco
	public function save()
	{
		
		$sql = new Sql();
		
		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
			":idcart"=>$this->getidcart(),
			":dessessionid"=>$this->getdessessionid(),
			":iduser"=>$this->getiduser(),
			":deszipcode"=>$this->getdeszipcode(),
			":vlfreight"=>$this->getvlfreight(),
			":nrdays"=>$this->getnrdays()
		]);
		
		$this->setData($results[0]);
		
	}
	
	//Método para adicionar o produto no carrinho (recebe uma instância da classe Product)
	public function addProduct(Product $product)
	{
		
		$sql = new Sql();
		
		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES(:idcart, :idproduct)", [
			":idcart"=>$this->getidcart(),
			":idproduct"=>$product->getidproduct()		
		]);
		
	}
	
	//Método para remover produtos do carrinho. Passando o objeto produto e variável $all. $all representa todos os produtos do mesmo tipo dentro de um carrinho, false por padrão.
	public function removeProduct(Product $product, $all = false)
	{
		$sql = new Sql();
		
		//Se $all é verdadeiro é feito o update do campo dtremoved na tb_cartsproducts
		if ($all) {
			
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
				":idcart"=>$this->getidcart(),
				":idproduct"=>$product->getidproduct()
			]);
		
		//Senão, é removido apenas um produto
		} else {
			
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
				":idcart"=>$this->getidcart(),
				":idproduct"=>$product->getidproduct()
			]);			
			
			
		}
		
	}
	
	//Método para listagem de produtos no carrinho
	public function getProducts()
	{
		
		$sql = new Sql();
		
		$rows = $sql->select("
			SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal 
			FROM tb_cartsproducts a 
			INNER JOIN tb_products b ON a.idproduct = b.idproduct 
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
			GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl 
			ORDER BY b.desproduct
		", [
			":idcart"=>$this->getidcart()
		]);
		
		return Product::checkList($rows);
		
	}
}

?>