<?php

namespace Hcode;

//Namespace nativo do Rain TPL
use Rain\Tpl;

class Page {
	
	private $tpl;
	private $options = [];
	private $defaults = [
		"data"=>[]
	];
	
	public function __construct($opts = array(), $tpl_dir = "/views/"){
		
		$this->options = array_merge($this->defaults, $opts);
		// config
		$config = array(	//$_SERVER DOCUMENT_ROOT (variável de ambiente) pega o diretório ROOT dentro do array superglobal $_SERVER
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]. $tpl_dir,
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]. "/views-cache/",
			"debug"         => false // set to false to improve the speed
		   );

		Tpl::configure( $config );
		
		$this->tpl = new Tpl;

		$this->setData($this->options["data"]);
		
		$this->tpl->draw("header");	
	
	}
	
	private function setData($data = array())
	{
		foreach ($data as $key => $value){
			$this->tpl->assign($key, $value);
		}
		
	}
	
	public function setTpl($name, $data = array(), $returnHTML = false)
	{
		
		$this->setData($data);
		
		return $this->tpl->draw($name, $returnHTML);
		
	}
	
	public function __destruct(){
		
		$this->tpl->draw("footer");
		
	}
}

?>