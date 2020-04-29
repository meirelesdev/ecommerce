<?php 
session_start();

require_once("vendor/autoload.php");


use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new \Slim\Slim();

$app->config('debug', true);

$app->get('/', function() {
	
	$page = new Page();

	$page->setTpl("index");

});

// Rota para listar todos os usuarios
$app->get("/admin/users", function() {
	// Verifica se o usuario que chamou a rota esta logado
	User::verifyLogin();
	// faz um select nos usuarios e retorna
	$users = User::listAll();	
	$page = new PageAdmin();
	// Carrega o template users enviando a lista com todos os usuarios
	$page->setTpl("users", array(
		"users"=>$users
	));

});

$app->get('/admin', function(){
	
	User::verifyLogin();
	// $user = $_SESSION[User::SESSION];
	// var_dump($user);
	// exit;
	$page = new PageAdmin();

	$page->setTpl("index");
	
});

$app->get('/admin/login', function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl('login');
});
$app->post('/admin/login', function() {
	
	User::login($_POST["login"], $_POST["password"]);
	
	header("Location: /admin");
	exit;
});

$app->get('/admin/logout', function() {
	User::logout();

	header("Location: /admin/login");
	exit;
});
// Rota para criar usuarios, carrega o formulario!
$app->get("/admin/users/create", function() {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");

});
// Rota para salvar os dados enviados da rota de cima
$app->post("/admin/users/create", function() {
	
	User::verifyLogin();

	$user = new User();
	
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
	
	$user->setData($_POST); 

	$user->save();
		
	header("Location: /admin/users");
	exit;   

});

// Rota para deletar um ususario
$app->get("/admin/users/:iduser/delete", function($iduser) {
	
	User::verifyLogin();
	
	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;
 
});

// Rota para editar um usuario, carrega o formulario com os
// Dados atuais.
$app->get("/admin/users/:iduser", function($iduser) {

	User::verifyLogin();
	$user = new User();
	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));

});

//Rota para salvar a edição do usuario, envia os novos dados para o banco
$app->post("/admin/users/:iduser", function($iduser) {
	
	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;
	
	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");
	exit;

});

$app->get("/admin/forgot", function(){

	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);

	$page->setTpl("forgot");

});

$app->post("/admin/forgot" , function() {
	
	User::getForgot($_POST['email']);

	header("Location: /admin/forgot/sent");
	
	exit;
	
});

$app->get("/admin/forgot/sent", function() {
	
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");

});

$app->get('/admin/forgot/reset', function(){

	$user = User::validForgotDecrypt($_GET['code']);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));

});

$app->post("/admin/forgot/reset", function() {

	$forgot = User::validForgotDecrypt($_POST['code']);
	
	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();
	
	$user->get((int)$forgot["iduser"]);

	$user->setPassword($_POST["password"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");
});


$app->run();

?>
