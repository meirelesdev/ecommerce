<?php



use \Hcode\Page;
use \Hcode\Model\User;



$app->get("/login", function(){

	$page = new Page();

	$page->setTpl('login', [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),
		'registerValues'=>(isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name'=>'', 'email'=>'','phone'=>'']
	]);

});

$app->post("/login", function(){

    try{

        User::login($_POST['login'], $_POST['password']);
        
    } catch(Exception $e) {
        
        User::setError($e->getMessage());
    }	
    header("Location: /checkout");
    exit;


});

$app->get('/logout', function() {

    User::logout();

    header("Location: /login");
    exit;

});

$app->post('/register', function() {

        $_SESSION['registerValues'] = $_POST;

        if(!isset($_POST['name']) || $_POST['name'] == ''){
            User::setErrorRegister("Preenchar o seu nome.");
            header("Location: /login");
            exit;

        }
        if(!isset($_POST['email']) || $_POST['email'] == ''){
            User::setErrorRegister("Preenchar o seu E-mail.");
            header("Location: /login");
            exit;

        }
        if(!isset($_POST['password']) || $_POST['password'] == ''){
            User::setErrorRegister("Preenchar a sua senha.");
            header("Location: /login");
            exit;

        }
        if(User::checkLoginExist($_POST['email']) ){
            User::setErrorRegister("Este E-mail já esta cadastrado para outro usuario.");
            header("Location: /login");
            exit;
        }
        if( !isset($_POST['phone']) || $_POST['phone'] == '' ){

            $_POST['phone'] = 000000000;

        }

        $user = new User();

        $user->setData([
            'inadmin'=>0,
            'deslogin'=>$_POST['email'],
            'desperson'=>$_POST['name'],
            'desemail'=>$_POST['email'],
            'despassword'=>$_POST['password'],
            'nrphone'=>$_POST['phone']
        ]);
        // print_r($user);
        // exit;
        $user->save();

        User::login($_POST['email'], $_POST['password']);

        header("Location: /checkout");
        exit;

});


$app->get("/profile", function(){

	User::verifyLogin(false);
	
	$user = User::getFromSession();
	
	$user->getValues();
	
	$page = new Page();
	
	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);
});


$app->post("/profile", function(){

	User::verifyLogin(false);
	if( !isset($_POST['desperson']) || $_POST['desperson'] === '' ) {
		
		User::setError("Preencha o seu nome.");
		header("Location: /profile");
		exit;

	}

	if(!isset($_POST['desemail']) || $_POST['desemail'] === '') {
		
		User::setError("Preencha o seu E-mail.");
		header("Location: /profile");
		exit;

	}

	$user = User::getFromSession();

	if($_POST['desemail'] !== $user->getdesemail()) {
		if (User::checkLoginExist($_POST['desemail']) === true) {
			
			User::setError("Este endereço dee-mail ja esta cadastrado para outro usuario.");
			
			header("Location: /profile");
			exit;
		}
	}

	$_POST['inadmin'] = $user->getinadmin();
	$_POST['despassword'] = $user->getdespassword();
	$_POST['deslogin'] = $_POST['desemail'];
	
	$user->setData($_POST);
	print
	$user->update();

	User::setSuccess("Dados alterados com sucesso.");

	header("Location: /profile");
	exit;
});

?>
