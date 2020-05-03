<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{

    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret";
    const SECRET_IV = "HcodePhp7_Secret_IV";
    const ERROR = 'UserError';
    const ERROR_REGISTER = "UserErrorRegister";
    const SUCCESS = 'UserSuccess';

    public static function getFromSession() {
        
        $user = new User();

        if( isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0 ) {


            $user->setData($_SESSION[User::SESSION]);
            
        }

        return $user;

    }

    public static function verifyLogin($inadmin = true) {
        
        // var_dump(User::checkLogin($inadmin));
        // exit; ISSO RETORNOU FALSO
        if(!User::checkLogin($inadmin)) {
            if($inadmin){
                header("Location: /admin/login");
                exit;
            }else{
                header("Location: /login");
                exit;
            }
        }
    }

    public static function checkLogin($inadmin = true){
        
        if(
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
        ) {
            
            //Não esta logado
            return false;
        } else {
            //Verifica se esta logado e se é administrador.
            
            if( $inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true )  {
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
        
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :login", array(
            ":login"=>$login
        ));
        
        if(count($results) === 0){
            throw new \Exception("Error: Usuário inexistente!");
        }
        
        $data = $results[0];

        if (password_verify($password, $data["despassword"]) === true){
            
            $user = new User();
            
            
            $data['desperson'] = utf8_encode($data['desperson']);
            
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
        
        
        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", [
           ":desperson"     => utf8_decode($this->getdesperson()),
           ":deslogin"      => $this->getdeslogin(),
           ":despassword"   => User::getPasswordHash($this->getdespassword()),
           ":desemail"      => $this->getdesemail(),
           ":nrphone"       => $this->getnrphone(),
           ":inadmin"       => $this->getinadmin()
        ]);

        
        $this->setData($results[0]);

    }

    public function get($iduser) {

        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser"=>$iduser
        ));
        $data = $results[0];

        $data['desperson'] = utf8_encode($data['desperson']);

        $this->setData($data);
    }

    public function update() {
        
        $sql = new Sql();
        
        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"       =>$this->getiduser(),
            ":desperson"    =>utf8_decode($this->getdesperson()),
            ":deslogin"     =>$this->getdeslogin(),
            ":despassword"  =>$this->getdespassword(),
            ":desemail"     =>$this->getdesemail(),
            ":nrphone"      =>$this->getnrphone(),
            ":inadmin"      =>$this->getinadmin()
        ));

        $_SESSION[User::SESSION] = $this->getValues();
        

    }

    public static function getPasswordHash($password) {
        
        return password_hash($password, PASSWORD_DEFAULT, [
            'cost'=>12
        ]);
    }
    public static function getForgot($email, $inadmin = true) {
        
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM tb_persons a 
                            INNER JOIN tb_users b USING(idperson) 
                            WHERE a.desemail = :EMAIL;
                            ", [
                             ":EMAIL"=>$email
                            ]);

        if(count($results) === 0) {
            throw new \Exception("Não foi possivel recuperar a senha!");
        }else{            

            $data = $results[0];

            $resultRecover = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser"=> $data["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"]
            ));

            if( count($resultRecover) === 0 ){                
                throw new \Exception("Não foi possivel gerar nova senha!");
            } else{
                $dataRecovery = $resultRecover[0];

                $code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));
                
                $code = base64_encode($code);
                
                if($inadmin === true){

                    $link = "http://local.lojahcode.com/admin/forgot/reset?code=".$code;
                
                } else {
                
                    $link = "http://local.lojahcode.com/forgot/reset?code=".$code;
                }
                
                //public function __construct($toAddress, $toName, $subjec, $tplName, $data = array)                    
				$mailer = new  Mailer($data["desemail"], $data["desperson"], "Redefinir Senha!", "forgot", [
					"name"=>$data["desperson"],
					"link"=>$link
                ]);

                $mailer->send();
                
				return $link    ;
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

    public function delete() {

        $sql = new Sql();

        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser" => $this->getiduser()
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

    public static function setError($msg){

        $_SESSION[User::ERROR] = $msg;
    }

    public static function getError() {


        $msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR]: '';

        User::clearError();
        return $msg;
    }

    public static function setSuccess($msg) {

        $_SESSION[User::SUCCESS] = $msg;
    }

    public static function getSuccess() {

        $msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';

        User::clearSuccess();

        return $msg;
    }

    public static function clearSuccess() {

        $_SESSION[User::SUCCESS] = null;
    }

    public static function clearError() {

        $_SESSION[User::ERROR] = NULL;
    }

    public static function setErrorRegister($msg) {

        $_SESSION[User::ERROR_REGISTER] = $msg;
        
    }
    
    public static function getErrorRegister(){

        $msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';

        User::clearErrorRegister();
        
        return $msg;
    }

    public static function clearErrorRegister(){

        $_SESSION[User::ERROR_REGISTER] = null;
    }

    public static function checkLoginExist($login){
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
            ':deslogin'=>$login
        ]);

        return (count($results) > 0);

        
    }


}

?>
