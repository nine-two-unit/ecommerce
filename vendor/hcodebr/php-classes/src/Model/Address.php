<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Address extends Model {
	
	const SESSION_ERROR = "AddressError";
	
	//Método para fazer a consulta no WebService ViaCEP e carregar as informações de endereço pelo número do CEP
	public static function getCEP($nrcep)
	{
		
		//remove os traços e deixa apenas números
		$nrcep = str_replace("-", "", $nrcep);
		
		//inicia o CURL
		$ch = curl_init();
		
		//Opções de chamada do curl, chamando URL
		curl_setopt($ch, CURLOPT_URL, "https://viacep.com.br/ws/$nrcep/json/");
		
		//Opção de retorno dos dados
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//Opção para a não-verificação de SSL
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$data = json_decode(curl_exec($ch), true);
		
		//Fecha o curl
		curl_close($ch);
		
		return $data;
		
	}
	
	//Método para carregar as informações retornadas no objeto 
	public function loadFromCEP($nrcep)
	{
		
		$data = Address::getCEP($nrcep);
		
		if(isset($data["logradouro"]) && $data["logradouro"]){
			
			$this->setdesaddress($data["logradouro"]);
			$this->setdescomplement($data["complemento"]);
			$this->setdesdistrict($data["bairro"]);
			$this->setdescity($data["localidade"]);
			$this->setdesstate($data["uf"]);
			$this->setdescountry("Brasil");
			$this->setdeszipcode($nrcep);
		}
		
	}
	
	public function save()
	{
		
		$sql = new Sql();
		
		$results = $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict)", [
			":idaddress"=>$this->getidaddress(),
			":idperson"=>$this->getidperson(),
			":desaddress"=>$this->getdesaddress(),
			":descomplement"=>$this->getdescomplement(),
			":descity"=>$this->getdescity(),
			":desstate"=>$this->getdesstate(),
			":descountry"=>$this->getdescountry(),
			":deszipcode"=>$this->getdeszipcode(),
			":desdistrict"=>$this->getdesdistrict()
		]);
		
		if(count($results) > 0){
			$this->setData($results[0]);			
		}
	}
	
	//Método para atribuir mensagem de erro ao array $_SESSION
	public static function setMsgError($msg)
	{
		
		$_SESSION[Address::SESSION_ERROR] = $msg;
		
	}
	
	//Método para retornar o erro
	public static function getMsgError()
	{
		
		$msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";
		
		Address::clearMsgError();
		
		return $msg;
		
	}
	
	//Método para limpar a constante de sessão com o erro
	public static function clearMsgError()
	{
		
		$_SESSION[Address::SESSION_ERROR] = NULL;
		
	}
	
}

?>