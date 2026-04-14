<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ElementoController;
use App\Controllers\HomeController;
use App\Controllers\PersonaController;
use App\Controllers\TareaController;
use App\Controllers\UsuarioController;

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

$router->get('/admin/personas', PersonaController::class, 'index');
$router->get('/admin/personas/agregar', PersonaController::class, 'agregar');
$router->post('/admin/personas/guardar', PersonaController::class, 'guardar');
$router->get('/admin/personas/{id}/editar', PersonaController::class, 'editar');
$router->post('/admin/personas/{id}/actualizar', PersonaController::class, 'actualizar');
$router->get('/admin/personas/{id}/eliminar', PersonaController::class, 'eliminar');
$router->post('/admin/personas/{id}/borrar', PersonaController::class, 'borrar');

$router->get('/admin/usuarios', UsuarioController::class, 'index');
$router->get('/admin/usuarios/agregar', UsuarioController::class, 'agregar');
$router->post('/admin/usuarios/guardar', UsuarioController::class, 'guardar');
$router->get('/admin/usuarios/{id}/editar', UsuarioController::class, 'editar');
$router->post('/admin/usuarios/{id}/actualizar', UsuarioController::class, 'actualizar');
$router->get('/admin/usuarios/{id}/eliminar', UsuarioController::class, 'eliminar');
$router->post('/admin/usuarios/{id}/borrar', UsuarioController::class, 'borrar');
