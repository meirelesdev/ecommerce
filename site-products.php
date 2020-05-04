<?php

use \Hcode\Page;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

$app->get("/categories/:idcategory", function($idcategory){
	
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1 ;
	
	$category = new Category();
	
	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);
	
	$pages = [];
	
	for($i=1; $i <= $pagination['pages'];$i++){
		$link = '/categories/'.$category->getidcategory().'?page='.$i;
 		array_push($pages, [
			'link'=> $link,
			'page'=>$i
		]);
	}
	
	$page = new Page();
	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>$pagination['data'],
		'pages'=>$pages
	]);

});

$app->get("/products/:desurl", function($desurl){

	$product = new Product();

	$product->getFromURL($desurl);

	$page = new Page();

	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);

});

?>
