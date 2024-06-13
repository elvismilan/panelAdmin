<?php

use App\Controllers\LoginController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Router;

$router = new Router();

$router->get('/', LoginController::class, 'index');
$router->post('/login', AuthController::class, 'index');

$router->get('/dashboard', DashboardController::class, 'index');
//$router->get('/dashboard', DashboardController::class, 'sample');

$router->dispatch();