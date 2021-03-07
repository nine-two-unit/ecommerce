<?php

namespace Hcode;

//Namespace nativo do Rain TPL
use Rain\Tpl;

class Page {
	
	private $tpl;
	private $options = [];
	
	//atributo defaults envia as opções header e footer como true por padrão, para que sejam carregadas na validação antes do draw
	private $defaults = [
		"header"=>true,
		"footer"=>true,
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
		
		if ($this->options["header"] === true) $this->tpl->draw("header");	
	
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
		
		if ($this->options["footer"] === true) $this->tpl->draw("footer");
		
	}
}

?>