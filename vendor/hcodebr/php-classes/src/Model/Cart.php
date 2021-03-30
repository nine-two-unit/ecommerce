<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model {
	
	//Funciona como ID do carrinho
	const SESSION = "Cart";
	
	//Constante das mensagens de erro do cálculo de frete
	const SESSION_ERROR = "CartError";
	
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
		
		$this->getCalculateTotal();
		
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
		
		$this->getCalculateTotal();
		
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
	
	//Método para retornar o valor total dos itens do carrinho
	public function getProductsTotals()
	{
		
		$sql = new Sql();
		
		$results = $sql->select("
			SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
			FROM tb_products a 
			INNER JOIN tb_cartsproducts b ON  a.idproduct = b.idproduct
			WHERE b.idcart = :idcart AND dtremoved IS NULL;
		", [
			":idcart"=>$this->getidcart()
		]);
		
		if(count($results) > 0){
			return $results[0];
		} else {
			return[];
		}
		
	}
	
	public function setFreight($nrzipcode)
	{
		
		$nrzipcode = str_replace("-", "", $nrzipcode);
		
		$totals = $this->getProductsTotals();
		
		if ($totals["nrqtd"] > 0) {
			
			//Altura mínima 2cm
			if($totals["vlheight"] < 2) $totals["vlheight"] = 2;
			
			//Comprimento mínimo 16cm
			if($totals["vllength"] < 16) $totals["vllength"] = 16;
			
			//Função Build query passa a query string em array com as variáveis necessárias
			$qs = http_build_query([
				"nCdEmpresa"=>"",
				"sDsSenha"=>"",
				"nCdServico"=>"40010",
				"sCepOrigem"=>"13207780",
				"sCepDestino"=>$nrzipcode,
				"nVlPeso"=>$totals["vlweight"],
				"nCdFormato"=>"1",
				"nVlComprimento"=>$totals["vllength"],
				"nVlAltura"=>$totals["vlheight"],
				"nVlLargura"=>$totals["vlwidth"],
				"nVlDiametro"=>"5",
				"sCdMaoPropria"=>"S",
				"nVlValorDeclarado"=>$totals["vlprice"],
				"sCdAvisoRecebimento"=>"S"
			]);
			
			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);
			//Retorna um objeto
			
			
			$result = $xml->Servicos->cServico;
			
			//Verifica se existem erro retornado do WebService do frete
			if($result->MsgErro != ""){
				
				Cart::setMsgError($result->MsgErro);
				
			} else {
				
				Cart::clearMsgError();
				
			}
			
			$this->setnrdays($result->PrazoEntrega);
			$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
			$this->setdeszipcode($nrzipcode);
			
			$this->save();
			
			return $result;
			
		} else {
			
			
		}
		
	}
	
	//Método para converter valores no formato aceito pelo banco
	public static function formatValueToDecimal($value):float
	{
		
		$value = str_replace(".", "", $value);
		return str_replace(",", ".", $value);
		
	}
	
	//Método para atribuir mensagem de erro ao array $_SESSION
	public static function setMsgError($msg)
	{
		
		$_SESSION[Cart::SESSION_ERROR] = $msg;
		
	}
	
	//Método para retornar o erro
	public static function getMsgError()
	{
		
		$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";
		
		Cart::clearMsgError();
		
		return $msg;
		
	}
	
	//Método para limpar a constante de sessão com o erro
	public static function clearMsgError()
	{
		
		$_SESSION[Cart::SESSION_ERROR] = NULL;
		
	}
	
	//Método para atualizar o frete em caso de adição ou remoção
	public function updateFreight()
	{
		
		if ($this->getdeszipcode() != ""){
			
			$this->setFreight($this->getdeszipcode());
			
		}
		
	}
	
	//
	public function getValues()
	{
		
		$this->getCalculateTotal();
		
		return parent::getValues();
		
	}
	
	//Método para calulcar subtotal e total com frete
	public function getCalculateTotal()
	{
		
		$this->updateFreight();
		
		$totals = $this->getProductsTotals();
		
		$this->setvlsubtotal($totals["vlprice"]);
		$this->setvltotal($totals["vlprice"] + $this->getvlfreight());
		
	}

}

?>