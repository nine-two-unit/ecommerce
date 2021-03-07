<?php

namespace Hcode;

class Model {
	
	private $values = [];
	
	//Método mágico __call detecta toda vez que um método é chamado. Recebe como parâmtros o nome do método e os argumentos
	public function __call($name, $args)
	{
		
		$method = substr($name, 0, 3);
		$fieldName = substr($name, 3, strlen($name));
		
		switch ($method) {
			
			case "get":
				return $this->values[$fieldName];
			break;
			
			case "set":
				$this->values[$fieldName] = $args[0];
			break;
			
		}
		
	}
	
	public function setData($data = array())
	{
		
		foreach ($data as $key => $value) {
			
			// Chamando o método de uma forma dinâmica. Se utiliza entre chaves uma string referenciando o nome do método
			$this->{"set".$key}($value);
			
		}
		
		
	}
	
	public function getValues()
	{
		
		return $this->values;
		
	}
	
}


?>