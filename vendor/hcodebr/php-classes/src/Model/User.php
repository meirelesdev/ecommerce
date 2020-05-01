<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{

    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret";
    const SECRET_IV = "HcodePhp7_Secret_IV";

    public static function getFromSession() {
        
        $user = new User();

        if( isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0 ) {


            $user->setData($_SESSION[User::SESSION]);
            
        }

        return $user;

    }

    public static function verifyLogin($inadmin = true) {
        
        // print_r($_SESSION[User::SESSION]['inadmin']);
        // exit;
        
        if(
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
            ||
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
        ) {
            header("Location: /admin/login");
            exit;
        }
    }

    public static function checkLogin($inadmin = true){

        if(verifyLogin($inadmin)) {
            //Não esta logado
            return false;

        } else {
            //Verifica se esta logado e se é administrador.
            if( $inadmin === true && (bool)$_SESSION[User::SESSION]['inaddmin'] === true )  {
                return true;
            } else if ( $inadmin === false ) {
                //Esta logado mas não é administrador
                return true;
            } else {
                //Não esta logado nem como usuario nem administrador
                return false;
            }
        }
    }

    public static function login($login, $password){

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN"=>$login
        ));

        if(count($results) === 0){
            throw new \Exception("Error: Usuário inexistente!");
        }
        $data = $results[0];

        if (password_verify($password, $data["despassword"])){
            
            $user = new User();
            
            $user->setData($data);

            $_SESSION[User::SESSION] = $user->getValues();

            return $user;
        
        }else{
            throw new \Exception("Error: Senha invalida!");
        }
    }


    public static function logout(){

        $_SESSION[User::SESSION] = null;

    }

    public static function listAll(){
        
        $sql = new Sql();
        
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

    }
    public function save() {
        $sql = new Sql();
        
        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",
        array(
           ":desperson"     => $this->getdesperson(),
           ":deslogin"      => $this->getdeslogin(),
           ":despassword"   => User::getPasswordHash($this->getdespassword()),
           ":desemail"      => $this->getdesemail(),
           ":nrphone"       => $this->getnrphone(),
           ":inadmin"       => $this->getinadmin()
        ));
        $this->setData($results[0]);

    }

    public function get($iduser) {

        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser"=>$iduser
        ));
        $this->setData($results[0]);
    }

    public function update() {
        
        $sql = new Sql();
        
        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"       =>$this->getiduser(),
            ":desperson"    =>$this->getdesperson(),
            ":deslogin"     =>$this->getdeslogin(),
            ":despassword"  =>$this->getdespassword(),
            ":desemail"     =>$this->getdesemail(),
            ":nrphone"      =>$this->getnrphone(),
            ":inadmin"      =>$this->getinadmin()
        ));


    }

    public static function getPasswordHash($password) {
        
        return password_hash($password, PASSWORD_DEFAULT, [
            'cost'=>12
        ]);
    }

    public function delete() {

        $sql = new Sql();

        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser" => $this->getiduser()
        ));

    }

    public static function getForgot($email) {
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :EMAIL;", array(
            ":EMAIL"=>$email
        ));

        if(count($results) === 0) {
            throw new \Exception("Não foi possivel recuperar a senha!");
        }else{            
            $data = $results[0];

            $resultRecover = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser"=> $results[0]["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"]
            ));

            if(count($resultRecover) === 0){                
                throw new \Exception("Não foi possivel gerar nova senha!");
            } else{
                $dataRecovery = $resultRecover[0];
                $code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));
				$code = base64_encode($code);

                    $link = "http://local.lojahcode.com/admin/forgot/reset?code=".$code;
                
                    
				$mailer = new  Mailer($data["desemail"], $data["desperson"], "Redefinir Senha!", "forgot", array(
					"name"=>$data["desperson"],
					"link"=>$link
                ));

                $mailer->send();
                
				return $data;
			}
		}
    }

    public function validForgotDecrypt($code) {

        $code = base64_decode($code);

        $idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));
    
        $sql = new Sql();
        $results = $sql->select("SELECT * 
        FROM tb_userspasswordsrecoveries a 
        INNER JOIN tb_users b 
        USING(iduser) 
        INNER JOIN tb_persons c 
        USING(idperson) 
        WHERE a.idrecovery = :idrecorevy 
        AND a.dtrecovery IS NULL AND DATE_ADD(a.dtregister, INTERVAL 2 HOUR) >= NOW();", array(":idrecorevy"=>$idrecovery));
        
            if(count($results) === 0) {
                throw new \Exception("Não foi possivel recuperar a senha.");
            }else{
                return $results[0];
            }
    }
    public static function setForgotUsed($idrecovery){

        $sql =new Sql();

        $sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
            ":idrecovery"=>$idrecovery
        ));
    }

    public function setPassword($password) {

        $sql = new Sql();
        
        $hashPassword = User::getPasswordHash($password);

        $sql->query("UPDATE tb_users set despassword = :password WHERE iduser = :iduser", array(
            ":password"=>$hashPassword,
            ":iduser"=>$this->getiduser()
        ));
    }
}

?>
