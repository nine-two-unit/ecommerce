<?php

//Função para formatar preço
function formatPrice(float $vlprice)
{
	
	return number_format($vlprice, 2, ",", ".");
	
}
?>