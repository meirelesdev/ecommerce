<?php


use \Hcode\Page;
use \Hcode\Model\User;

// Rotas para recuperação de senhas

$app->get("/forgot", function(){

	$page = new Page();

	$page->setTpl("forgot");

});

$app->post("/forgot" , function() {
	
	User::getForgot($_POST['email'], false);

	header("Location: /forgot/sent");	
	exit;
	
});

$app->get("/forgot/sent", function() {
	
	$page = new Page();

	$page->setTpl("forgot-sent");

});

$app->get('/forgot/reset', function(){

	$user = User::validForgotDecrypt($_GET['code']);

	$page = new Page();

	$page->setTpl("forgot-reset", [
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	]);

});

$app->post("/forgot/reset", function() {

	$forgot = User::validForgotDecrypt($_POST['code']);
	
	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();
	
	$user->get((int)$forgot["iduser"]);

	$user->setPassword($_POST["password"]);

	$page = new Page();

	$page->setTpl("forgot-reset-success");
});


?>