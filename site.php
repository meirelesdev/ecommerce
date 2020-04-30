<?php

use \Hcode\Page;
use \Hcode\Model\Product;

$app->get('/', function() {

	$products = new Product();

	$products = Product::listAll();

	$page = new Page();

	$page->setTpl("index", [
		"products"=>Product::checkList($products)
	]);

});

?>