<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

// Rota para listar todos os usuarios
$app->get("/admin/users", function() {
	// Verifica se o usuario que chamou a rota esta logado
	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : '';

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1 ;

	if($search != '') {
		$pagination = User::getPageSearch($search, $page);
	} else {

		$pagination = User::getPage();
	}
	

	$pages = [];
	
	for ( $x = 0 ; $x < $pagination['pages']; $x++ ){
		
		array_push($pages, [
			'href'=>'/admin/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
			]);

	}

	$page = new PageAdmin();
	
	// Carrega o template users enviando a lista com todos os usuarios
	$page->setTpl("users", [
		"users"=>$pagination['data'],
		'search'=>$search,
		'pages'=>$pages
	]);

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

	$page->setTpl("users-update", [
		"user"=>$user->getValues()
	]);

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

?>
