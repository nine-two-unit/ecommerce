<?php

namespace Hcode;

class Model {
	
	//Atributo values recebe todos os dados dos campos do objeto
	private $values = [];
	
	//Método construído de forma dinâmica para tratar Getters e Setters automaticamente
	//Método mágico __call detecta toda vez que um método é chamado. Recebe como parâmtros o nome do método e os argumentos
	public function __call($name, $args)
	{
		
		$method = substr($name, 0, 3);//Substr lê 3 caracteres a partir da posição 0
		$fieldName = substr($name, 3, strlen($name));//Lê a partir da posição 3 até ao final, contando os caracteres com strlen
		
		//Switch lê a variável method para identificar qual é o tipo de método que está sendo chamado
		switch ($method) {
			
			//Case get pega a variável privada $values e procura o campo $fieldName dentro dela, encontrado é apenas retornada
			case "get":
				return $this->values[$fieldName];
			break;
			
			//Case set pega procura na variável privada $values, o campo $fieldname e atribui o valor que está chegando na posição 0 de $args
			case "set":
				$this->values[$fieldName] = $args[0];
			break;
			
		}
		
	}
	
	//Método setData pega os itens do array retornado do banco e transforma em chave e valor
	public function setData($data = array())
	{
		
		foreach ($data as $key => $value) {
			
		// PERSONALIZADO - PARAR GUARDAR A SENHA COMO HASH NO SETTER
		if($key === "despassword") {
			$value = password_hash($value, PASSWORD_BCRYPT, ["cost"=>12]);
		}
			// Chamando o método de uma forma dinâmica. Se utiliza entre chaves uma string referenciando o nome do método
			$this->{"set".$key}($value);
			

		}
		
		
	}
	
	//Método getValues retorna o atributo privado $values
	public function getValues()
	{
		
		return $this->values;
		
	}
	
}


?>