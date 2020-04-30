<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

// Rota para listar todos os Produtos
$app->get("/admin/products", function() {
	// Verifica se o usuario que chamou a rota esta logado
	User::verifyLogin();
	// faz um select nos produtos e retorna
	$products = Product::listAll();	
	
	$page = new PageAdmin();

	// Carrega o template products enviando a lista com todos os produtos
	$page->setTpl("products", [
		"products"=>$products
	]);

});
//Rota de criação de novos produtos
$app->get("/admin/products/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("products-create");

});
//Rota para enviar novo produto para o banco de dados
$app->post("/admin/products/create", function(){

	User::verifyLogin();

	$product = new Product();

	$product->setData($_POST);

	$product->save();

	header("Location: /admin/products");
	exit;

});
// Rota para editar produto
$app->get("/admin/products/:idproduct", function($idproduct) {
		
	User::verifyLogin();
	
	$product = new Product();

	$product->get((int)$idproduct);
	
	$page = new PageAdmin();
	
	$page->setTpl("products-update", [
			"product"=>$product->getValues()
		]);

});

// Rota para deletar um Produto
$app->get("/admin/products/:idproduct/delete", function($idproduct) {
	
	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);
	
	$product->delete();

	header("Location: /admin/products");
	exit;

});


// Rota para enviar o produto editado para o banco de dados
$app->post("/admin/products/:idproduct", function($idproduct) {
		
	User::verifyLogin();
	
	$product = new Product();	
	
	$product->get((int)$idproduct);
	
	$product->setData($_POST);
	
	$product->save();

	$product->setPhoto($_FILES["file"]);

	header("Location: /admin/products");
	exit;

});
 

?>
