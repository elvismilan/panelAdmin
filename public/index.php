<?php

//require '../config.php';
//require '../vendor/autoload.php';

//$router = require '../src/Routes/index.php';

require_once '../vendor/autoload.php';

use Core\Router;

// Initialize the router
$router = new Router();

// Define routes
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('home/index', ['controller' => 'Home', 'action' => 'index']);
// Add more routes as needed

// Dispatch the request
$url = isset($_GET['url']) ? $_GET['url'] : '';
$router->dispatch($url);