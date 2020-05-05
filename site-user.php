<?php

use \Hcode\Page;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\Cart;

$app->get("/login", function(){
    
    // print_r(User::getPasswordHash("admin"));
    // exit;

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
    header("Location: /profile");
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
        
        $user->save();

        User::login($_POST['email'], $_POST['password']);

        header("Location: /checkout");
        exit;

});

$app->get("/profile/change-password", function() {

    User::verifyLogin(false);

    $page = new Page();

    $page->setTpl("profile-change-password", [
        'changePassError'=>User::getError(),
        'changePassSuccess'=>User::getSuccess()
    ]);

});

$app->post("/profile/change-password", function() {
    
    User::verifyLogin(false);
    
    if ( !isset($_POST['current_pass']) || $_POST['current_pass'] === '' ) {
        User::setError("Digite a senha atual.");
        header("Location: /profile/change-password");
        exit;
    }

    if ( !isset($_POST['new_pass']) || $_POST['new_pass'] === '' ) {
        User::setError("Digite a nova senha.");
        header("Location: /profile/change-password");
        exit;
    }

    if ( !isset($_POST['new_pass_confirm']) || $_POST['new_pass_confirm'] === '' ) {
        User::setError("Confirme a nova senha.");
        header("Location: /profile/change-password");
        exit;
    }

    if ( $_POST['current_pass'] === $_POST['new_pass'] ) {
        User::setError("A sua nova senha deve ser diferente da atual.");
        header("Location: /profile/change-password");
        exit;
    }

    if ( $_POST['new_pass'] !== $_POST['new_pass_confirm'] ) {
        User::setError("A nova senha e confirmação estão diferentes.");
        header("Location: /profile/change-password");
        exit;
    }

    $user = User::getFromSession();

    if ( !password_verify($_POST['current_pass'], $user->getdespassword())) {

        User::setError("A senha esta á inválida.");
        header("Location: /profile/change-password");
        exit;
    }

    $user->setdespassword($_POST['new_pass']);

    $user->update();
    
    User::setSuccess("Senha alterada com sucesso.");
    header("Location: /profile/change-password");
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

$app->get("/profile/orders", function(){
    
    User::verifyLogin(false);

    $user = User::getFromSession();
    
    $page = new Page();
    
    $page->setTpl("profile-orders", [
		'orders'=>$user->getOrders()		
	]);
});
$app->get("/profile/orders/:idorder", function($idorder){
    
    User::verifyLogin(false);
    
    $user = new User();
    $user = User::getFromSession();
    
    $order = new Order();
    $order->get((int)$idorder);
    
    
    $cart = new Cart();
    
    $cart->get((int)$order->getidcart());


    $page = new Page();
    
    $page->setTpl("profile-orders-detail", [
        'order'=>$order->getValues(),
        'cart'=>$cart->getValues(),
        'products'=>$cart->getProducts()
	]);
});
?>
