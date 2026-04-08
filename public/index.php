<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use Core\App;
use Core\Router;

$app = new App(new Router());

$router = $app->getRouter();
require_once __DIR__ . '/../routes/web.php';

$app->run();