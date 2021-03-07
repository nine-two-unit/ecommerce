<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model {
	
	//Constante SESSION recupera os dados do array User
	const SESSION = "User";
	
	//Método de login, recebe $login e $password pelo post.
	public static function login($login, $password)
	{
		
		$sql = new Sql();
		
		//Consulta no login infromado
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));
		
		//Verifica se o resultado retornou algo ou está vazio
		if (count($results) === 0)
		{
			throw new \Exception("Usuário inexistente ou senha inválida!");//A contra-barra na Exception é necessária para voltar ao escopo principal
		}
		
		//Armazena em $data os results retornado no select ao banco
		$data = $results[0];
		
		//Função password_verify compara os hashs e retorna um booleano, no exemplo utilizando os valores da chave "despassword" armazenada no array $data
		if (password_verify($password, $data["despassword"]) === true)
		{
			
			$user = new User();
			
			//Método para detectar e separar campos e valores do array recebido da consulta SQL
			$user->setData($data);
			
			//Criação da sessão de login
			$_SESSION[User::SESSION] = $user->getValues();

			return $user;
			
		} else {
			
			throw new \Exception("Usuário inexistente ou senha inválida!");
			
		}
		
		
	}
	
	//Método de verificação do login
	public static function verifyLogin($inadmin = true) 
	{
		
		if(
			!isset($_SESSION[User::SESSION]) //Se a sessão não foi definida
			||
			!$_SESSION[User::SESSION] //Se a sessão estiver vazia ou falsa
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 //Verifica se o ID do usuário é um int maior que 0
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin //Verifica se o usuário tem permissão de admin
			
		){
			
			//Retorna ao login caso algumas das condições do If sejam satisfeitas
			header("Location: /admin/login");
			exit;
		}
		
	}
	
	public static function logout()
	{
		
		$_SESSION[User::SESSION] = NULL;
	
	}
	
	public static function listAll()
	{
		
		$sql = new Sql();
		
		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
		
	}
	
	public function save()
	{
		
		$sql = new Sql();
		
		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()		
		));
		
		$this->setData($results[0]);
		
	}
	
	public function get($iduser)
	{
		
		$sql = new Sql();
		
		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser
		));
		
		$this->setData($results[0]);
		
	}
	
	public function update()
	{
		
		$sql = new Sql();
		
		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()		
		));
		
		$this->setData($results[0]);
		
	}
	
	public function delete()
	{
		
		$sql = new Sql();
		
		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));
		
	}
	
}

?>