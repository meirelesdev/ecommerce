<?php 
session_start();
require_once __DIR__ . "/../vendor/autoload.php";
use \Slim\Slim;
$app = new Slim();
$app->config('debug', true);
require_once __DIR__ . "/../functions.php";
require_once __DIR__ . "/../admin-categories.php";
require_once __DIR__ . "/../admin-products.php";
require_once __DIR__ . "/../admin-forgot.php";
require_once __DIR__ . "/../admin-orders.php";
require_once __DIR__ . "/../admin-users.php";
require_once __DIR__ . "/../admin.php";
require_once __DIR__ . "/../site-checkout.php";
require_once __DIR__ . "/../site-products.php";
require_once __DIR__ . "/../site-user.php";
require_once __DIR__ . "/../site-cart.php";
require_once __DIR__ . "/../site.php";

$app->run();