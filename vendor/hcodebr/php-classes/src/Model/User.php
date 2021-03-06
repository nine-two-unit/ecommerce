<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model {
	
	//Constante SESSION recupera os dados do array User
	const SESSION = "User";
	
	//1ª Constante segredo da encriptação
	const SECRET = "Electr1c-5t@r*92";
	
	//2ª Constante segredo da encriptação
	const SECRET_II = "Pr0ton+Elctr0n+-";
	
	//Constante de sessão para erros
	const ERROR = "UserError";
	
	//Constante de sessão para erros de cadastro
	const ERROR_REGISTER = "UserErrorRegister";
	
	//Constante de sessão para mensagens gerais
	const SUCCESS = "UserSuccess";
	
	//Método que retorna o objeto usuário se o usuário está logado
	public static function getFromSession()
	{
			
		$user = new User();
		
		if(isset($_SESSION[User::SESSION]) && $_SESSION[User::SESSION]["iduser"] > 0){
			
			$user->setData($_SESSION[User::SESSION]);
			
		}
			
		return $user;
			
	}
	
	//Método para verificar login
	public static function checkLogin($inadmin = true)
	{
		if(
			!isset($_SESSION[User::SESSION]) //Se a sessão não foi definida
			||
			!$_SESSION[User::SESSION] //Se a sessão estiver vazia ou falsa
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 //Verifica se o ID do usuário é um int maior que 0
		){
			//Usuário não está logado
			return false;
			
		} else {
			
			//Verifica uma rota da administração
			if($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true){
				
				return true;
				
			} else if ($inadmin === false){
				
				return true;
				
			} else {
				
				return false;
			}
		}
		
	}
	
	//Método de login, recebe $login e $password pelo post.
	public static function login($login, $password)
	{
		
		$sql = new Sql();
		
		//Consulta no login infromado
		
		/*
		
		Nota: O código abaixo estava sendo utilizado anteriormente, porém no método getUserName() dentro de functions.php o campo deesperson não estava presente no objeto $user. Foi alterada a query de login para carregar o os dados da tabela tb_persons utilizando um INNER JOIN entre o iduser e idperson
		
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));
		*/

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.deslogin = :LOGIN", array(
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
			
			/*
			Caso hajam erros de codificação no nome do usuário, utilizar a função urf8_encode
			
			$data["desperson"] = utf8_encode($data["desperson"]);
			
			*/
			
			//Método para detectar e separar campos e valores do array recebido da consulta SQL
			$user->setData($data);
			
			//Criação da sessão de login
			$_SESSION[User::SESSION] = $user->getValues();
			
			/*var_dump($_SESSION);
			exit;*/
			return $user;
			
		} else {
			
			throw new \Exception("Usuário inexistente ou senha inválida!");
			
		}
		
		
	}
	
	//Método de verificação do login
	public static function verifyLogin($inadmin = true) 
	{
		
		if(!User::checkLogin($inadmin)){
			
			if($inadmin){
				header("Location: /admin/login");
			} else {
				header("Location: /login");
			}
			exit;
		
		}
		/* 
		#######################################################################
					Código antigo
					
		if(
			!isset($_SESSION[User::SESSION]) //Se a sessão não foi definida
			||
			!$_SESSION[User::SESSION] //Se a sessão estiver vazia ou falsa
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 //Verifica se o ID do usuário é um int maior que 0
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin //Verifica se o inadmin não é igual a $inadmin passada no método
		){
			
			//Retorna ao login caso algumas das condições do If sejam satisfeitas
			header("Location: /admin/login");
			exit;
		}
		#######################################################################
		*/
		
	}
	
	//Método de logout
	public static function logout()
	{
		
		$_SESSION[User::SESSION] = NULL;
	
	}
	
	//Método de listagem de usuários
	public static function listAll()
	{
		
		$sql = new Sql();
		
		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
		
	}
	
	//Método para salvar novos logins no DB
	public function save()
	{
		
		$sql = new Sql();
		
		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			//":despassword"=>User::hashPass($this->getdespassword()),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()		
		));
		//O método setData contido na classe Model é chamado na execução da rota no arquivo index.php. Ele se encarrega de chamar os setters gerados dinamicamente no momento da execução e o método mágico __call os executa e seta os atributos. Os getters são chamados na execução do método save e os valores são passados para o select.
		$this->setData($results[0]);
		
	}
	
	//Recupera ID de usuário
	public function get($iduser)
	{
		
		$sql = new Sql();
		
		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser
		));
		
		$this->setData($results[0]);
		
	}
	
	//Método de edição de usuários no banco
	public function update()
	{
		
		$sql = new Sql();
		
		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			//":despassword"=>User::hashPass($this->getdespassword()),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()		
		));
		
		$this->setData($results[0]);
		
	}
	
	//Método de exclusão de usuários no banco
	public function delete()
	{
		
		$sql = new Sql();
		
		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));
		
	}
	
	//Método para recuperação de senha via e-mail	
	public static function getForgot($email, $inadmin = true)
	{
		
		//Verifica se o e-mail existe no banco
		$sql = new Sql();
		
		$results = $sql->select("
		SELECT * 
		FROM tb_persons a 
		INNER JOIN tb_users b USING(idperson)
		WHERE a.desemail = :email
		", array(
		":email"=>$email
		));
		
		if (count($results) === 0) {
			
			throw new \Exception("Não foi possível recuperar a senha!");
			
		} else {
			
			$data = $results[0];
			
			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"]			
			));
			
			//Sem resultados retornados, estoura um erro não específico
			if(count($results2) === 0){
				throw new \Exception("Não foi possível recuperar a senha!");
				
			} else {
				
				$dataRecovery = $results2[0];
				
				//Geração do código criptografado
				
				//Base64 é utilizado para converter os caracteres ilegíveis em texto, para que nenhum seja perdido
				$code = base64_encode(openssl_encrypt(
					$dataRecovery["idrecovery"],
					'AES-128-CBC',
					User::SECRET,
					0,
					User::SECRET_II					
				));
				
				//Verifica se o usuário é admin ou comum
				if($inadmin === true){
					
					//Se o $inadmin for true, o link gerado com ocódigo de recuperação da administração é gerado
					$link = "http://www.electricstar.com.br/admin/forgot/reset?code=$code";
					
				} else {
					
					//Senão, é gerado o link para usuários comuns
					$link = "http://www.electricstar.com.br/forgot/reset?code=$code";
					
				}
				
				
				
				//Construção do e-mail utilizando a classe Mailer
				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha da Electric Star Store", "forgot", array(
					"name"=>$data["desperson"],
					"link"=>$link
				));
				
				//Envio do e-mail
				$mailer->send();
				
				return $data;
				
				
				}
			
			
		}
	}
	
	//Método para validar o hash do link
	public static function validForgotDecrypt($code)
	{
		
		//Decodifica e decripta o idrecovery
		$idrecovery = openssl_decrypt(
		base64_decode($code),
		'AES-128-CBC',
		User::SECRET,
		0,
		User::SECRET_II					
	);
	
	$sql = new Sql();
	
	//Consulta no banco o idrecovery, se a data de recovery é NULL e se a data de registro tem menos de uma hora
	$results = $sql->select("
		SELECT * 
		FROM db_ecommerce.tb_userspasswordsrecoveries a 
		INNER JOIN tb_users b USING(iduser) 
		INNER JOIN tb_persons c USING(idperson) 
		WHERE 
			a.idrecovery = :idrecovery 
			AND 
			a.dtrecovery IS NULL 
			AND 
			DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();	
	", array(
		":idrecovery"=>$idrecovery
	));
	
	if (count($results) === 0){
		
		throw new \Exception("Não foi possível recuperar a senha.");
	
	} else {
		
		return $results[0];
		
		}
	
	}
	
	//Método que atualiza a dtrecovery a partir do momento que o usuário seta a nova senha
	public static function setForgotUsed($idrecovery)
	{
		
		$sql = new Sql();
		
		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));
		
	}
	
	//Método para realizar o update da senha redefinida no banco
	public function setPassword($password)
	{
		
		$sql = new Sql();
		
		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()		
		));
	}
	
	
	//Sessão de tratamento de erros
	
	public static function setError($msg)
	{
		
		$_SESSION[User::ERROR] = $msg;
		
	}
	
	public static function getError()
	{
		
		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : "";
		
		User::clearError();
		
		return $msg;
		
	}
	
	public static function clearError()
	{
		
		$_SESSION[User::ERROR] = NULL;
		
	}
	
	
	//Método para criptografar a senha em hash (utilizado nas páginas 'forgot')
    public static function hashPass($password)
    {
        
       return password_hash($password, PASSWORD_BCRYPT, [
			"cost"=>12
	   ]);

    }
	
	//Método para criptografar a senha em hash 
    /*
	public static function hashPass()
    {
        
        $_POST["despassword"] = password_hash($_POST["despassword"], PASSWORD_BCRYPT, ["cost"=>12]);

    }	*/
	
	//HASH PASSWORD TESTE (POST explícito na rota)
	/*public function hashPass($password)
	{
		
		if($password != $this->getdespassword()){
			
			return password_hash($password, PASSWORD_BCRYPT, [
				"cost"=>12
			]);
			
		} else {
			
			return $password;
			
		}
		
	}*/
	
	//Método para criptografar a senha.
	//No código anterior, a cada alteração de outros campos do usuário, o hash é criptografado novamente gerando uma nova senha.
	//O método pega a senha recebida no $_POST e compara com a senha no objeto antes da execução do SetData(). Se houver diferença é feito o hash da senha digitada armazenada no $_POST.
	//caso  a senha esteja igual, é retornado o mesmo valor armazenado no $_POST
	
	public function hashPassGet($password)
	{
		
		if($password != $this->getdespassword()){
			
			$_POST["despassword"] = password_hash($password, PASSWORD_BCRYPT, [
				"cost"=>12
			]);
			
		} else {
			
			return $_POST["despassword"] = $password;
			
		}
		
	}	
	
	//Métodos de tratamento de erros no cadastro do login
	
	public static function setErrorRegister($msg)
	{
		
		$_SESSION[User::ERROR_REGISTER] = $msg;
		
	}
	
	public static function getErrorRegister()
	{
		
		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : "";
		
		User::clearErrorRegister();
		
		return $msg;
		
	}
	
	public static function clearErrorRegister()
	{
		
		$_SESSION[User::ERROR_REGISTER] = NULL;
		
	}
	
	//Método para checar se o login digitado já existe
	public static function checkLoginExists($login)
	{
		
		$sql = new Sql();
		
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
			":deslogin"=>$login
		]);
		
		return (count($results) > 0);
		
	}
	
	//Métodos de tratamento de mensagens
	
	public static function setSuccess($msg)
	{
		
		$_SESSION[User::SUCCESS] = $msg;
		
	}
	
	public static function getSuccess()
	{
		
		$msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : "";
		
		User::clearSuccess();
		
		return $msg;
		
	}
	
	public static function clearSuccess()
	{
		
		$_SESSION[User::SUCCESS] = NULL;
		
	}
	
	public function getOrders()
	{
		
		$sql = new Sql();
		
		$results = $sql->select("
			SELECT * 
			FROM tb_orders a 
			INNER JOIN tb_ordersstatus b USING(idstatus)
			INNER JOIN tb_carts c USING(idcart)
			INNER JOIN tb_users d ON d.iduser = a.iduser
			INNER JOIN tb_addresses e USING(idaddress)
			INNER JOIN tb_persons f ON f.idperson = d.idperson
			WHERE a.iduser = :iduser
		", [
			":iduser"=>$this->getiduser()
		]);
		
		return $results;
		
	}
	
	public static function getPage($page = 1, $itemsPerPage = 10)
	{
		
		$start = ($page - 1) * $itemsPerPage;
		
		$sql = new Sql();
		
		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson) 
			ORDER BY b.desperson
			LIMIT $start, $itemsPerPage;
		");
		
		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
		
		return [
			"data"=>$results,
			"total"=>(int)$resultTotal[0]["nrtotal"],
			"pages"=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];
		
	}

	public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
	{
		
		$start = ($page - 1) * $itemsPerPage;
		
		$sql = new Sql();
		
		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS * 
			FROM tb_users a 
			INNER JOIN tb_persons b USING(idperson) 
			WHERE b.desperson LIKE :search OR b.desemail = :search OR a.deslogin LIKE :search 
			ORDER BY b.desperson
			LIMIT $start, $itemsPerPage;
		", [
			":search"=>"%".$search."%"
		]);
		
		$resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
		
		return [
			"data"=>$results,
			"total"=>(int)$resultTotal[0]["nrtotal"],
			"pages"=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
		];
		
	}	
}

?>