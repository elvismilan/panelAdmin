<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ElementoController;
use App\Controllers\GrupoController;
use App\Controllers\HomeController;
use App\Controllers\LogController;
use App\Controllers\NotificacionController;
use App\Controllers\PersonaController;
use App\Controllers\TareaController;
use App\Controllers\UsuarioController;

$router->get('/', HomeController::class, 'index');
$router->get('/login', AuthController::class, 'showLogin');
$router->post('/login', AuthController::class, 'login');
$router->post('/logout', AuthController::class, 'logout');
$router->get('/forgot-password', AuthController::class, 'showForgotPassword');
$router->post('/forgot-password', AuthController::class, 'processForgotPassword');
$router->get('/reset-password/{token}', AuthController::class, 'showResetPassword');
$router->post('/reset-password/{token}', AuthController::class, 'processResetPassword');
$router->get('/dashboard', DashboardController::class, 'index');

$router->get('/tareas', TareaController::class, 'index');
$router->get('/tareas/agregar', TareaController::class, 'agregar');
$router->post('/tareas/guardar', TareaController::class, 'guardar');
$router->get('/tareas/{id}/editar', TareaController::class, 'editar');
$router->post('/tareas/{id}/actualizar', TareaController::class, 'actualizar');
$router->get('/tareas/{id}/eliminar', TareaController::class, 'eliminar');
$router->post('/tareas/{id}/borrar', TareaController::class, 'borrar');

$router->get('/modulos', ElementoController::class, 'index');
$router->get('/modulos/agregar', ElementoController::class, 'agregar');
$router->post('/modulos/guardar', ElementoController::class, 'guardar');
$router->get('/modulos/{id}/editar', ElementoController::class, 'editar');
$router->post('/modulos/{id}/actualizar', ElementoController::class, 'actualizar');
$router->get('/modulos/{id}/eliminar', ElementoController::class, 'eliminar');
$router->post('/modulos/{id}/borrar', ElementoController::class, 'borrar');

$router->get('/personas', PersonaController::class, 'index');
$router->get('/personas/agregar', PersonaController::class, 'agregar');
$router->post('/personas/guardar', PersonaController::class, 'guardar');
$router->get('/personas/{id}/editar', PersonaController::class, 'editar');
$router->post('/personas/{id}/actualizar', PersonaController::class, 'actualizar');
$router->get('/personas/{id}/eliminar', PersonaController::class, 'eliminar');
$router->post('/personas/{id}/borrar', PersonaController::class, 'borrar');

$router->get('/usuarios', UsuarioController::class, 'index');
$router->get('/usuarios/agregar', UsuarioController::class, 'agregar');
$router->post('/usuarios/guardar', UsuarioController::class, 'guardar');
$router->get('/usuarios/{id}/editar', UsuarioController::class, 'editar');
$router->post('/usuarios/{id}/actualizar', UsuarioController::class, 'actualizar');
$router->get('/usuarios/{id}/eliminar', UsuarioController::class, 'eliminar');
$router->post('/usuarios/{id}/borrar', UsuarioController::class, 'borrar');

// Logs
$router->get('/logs', LogController::class, 'index');
$router->get('/logs/{id}/ver', LogController::class, 'ver');

// Notificaciones
$router->get('/notificaciones',              NotificacionController::class, 'index');
$router->get('/notificaciones/{id}/ver',     NotificacionController::class, 'ver');
$router->post('/notificaciones/{id}/leida',  NotificacionController::class, 'marcarLeida');

$router->get('/grupos', GrupoController::class, 'index');
$router->get('/grupos/agregar', GrupoController::class, 'agregar');
$router->post('/grupos/guardar', GrupoController::class, 'guardar');
$router->get('/grupos/{id}/editar', GrupoController::class, 'editar');
$router->post('/grupos/{id}/actualizar', GrupoController::class, 'actualizar');
$router->get('/grupos/{id}/eliminar', GrupoController::class, 'eliminar');
$router->post('/grupos/{id}/borrar', GrupoController::class, 'borrar');
