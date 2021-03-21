<?php

/*
######################################################
##													##
##	Rotas utilizadas pelas páginas site (principal)	##
##													##
######################################################
*/

use \Hcode\Page;

//Rota para a homepage
$app->get('/', function() {
    
	$page = new Page();
	
	$page->setTpl("index");
	
});


?>