<?php



use \Hcode\PageAdmin;
use \Hcode\Model\Product;
use \Hcode\Model\User;

// Rota para listar todos os Produtos
$app->get("/admin/products", function() {
	// Verifica se o usuario que chamou a rota esta logado
	User::verifyLogin();
	
	$search = (isset($_GET['search'])) ? $_GET['search'] : '';

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1 ;

	if($search != '') {
		$pagination = Product::getPageSearch($search, $page);
	} else {

		$pagination = Product::getPage();
	}
	

	$pages = [];
	
	for ( $x = 0 ; $x < $pagination['pages']; $x++ ){
		
		array_push($pages, [
			'href'=>'/admin/products?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
			]);

	}

	$page = new PageAdmin();
	
	// Carrega o template users enviando a lista com todos os usuarios
	$page->setTpl("products", [
		"products"=>$pagination['data'],
		'search'=>$search,
		'pages'=>$pages
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
