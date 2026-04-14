<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ElementoController;
use App\Controllers\HomeController;
use App\Controllers\TareaController;

$router->get('/', HomeController::class, 'index');
$router->get('/login', AuthController::class, 'showLogin');
$router->post('/login', AuthController::class, 'login');
$router->post('/logout', AuthController::class, 'logout');
$router->get('/dashboard', DashboardController::class, 'index');

$router->get('/admin/tareas', TareaController::class, 'index');
$router->get('/admin/tareas/agregar', TareaController::class, 'agregar');
$router->post('/admin/tareas/guardar', TareaController::class, 'guardar');
$router->get('/admin/tareas/{id}/editar', TareaController::class, 'editar');
$router->post('/admin/tareas/{id}/actualizar', TareaController::class, 'actualizar');
$router->get('/admin/tareas/{id}/eliminar', TareaController::class, 'eliminar');
$router->post('/admin/tareas/{id}/borrar', TareaController::class, 'borrar');

$router->get('/admin/modulos', ElementoController::class, 'index');
$router->get('/admin/modulos/agregar', ElementoController::class, 'agregar');
$router->post('/admin/modulos/guardar', ElementoController::class, 'guardar');
$router->get('/admin/modulos/{id}/editar', ElementoController::class, 'editar');
$router->post('/admin/modulos/{id}/actualizar', ElementoController::class, 'actualizar');
$router->get('/admin/modulos/{id}/eliminar', ElementoController::class, 'eliminar');
$router->post('/admin/modulos/{id}/borrar', ElementoController::class, 'borrar');
