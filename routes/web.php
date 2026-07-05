<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ElementoController;
use App\Controllers\GrupoController;
use App\Controllers\HomeController;
use App\Controllers\LogController;
use App\Controllers\NotificacionController;
use App\Controllers\ParametroController;
use App\Controllers\PersonaController;
use App\Controllers\TareaController;
use App\Controllers\UsuarioController;

// Determinar la ruta raíz según configuración APP_INDEX
$defaultIndex = $_ENV['APP_INDEX'] ?? 'home';
$indexRoute = match ($defaultIndex) {
    'login' => [AuthController::class, 'showLogin'],
    'home' => [HomeController::class, 'index'],
    default => [HomeController::class, 'index']
};

$router->get('/', $indexRoute[0], $indexRoute[1]);
$router->get('/login', AuthController::class, 'showLogin');
$router->post('/login', AuthController::class, 'login');
$router->post('/logout', AuthController::class, 'logout');
$router->get('/forgot-password', AuthController::class, 'showForgotPassword');
$router->post('/forgot-password', AuthController::class, 'processForgotPassword');
$router->get('/reset-password/{token}', AuthController::class, 'showResetPassword');
$router->post('/reset-password/{token}', AuthController::class, 'processResetPassword');
$router->get('/dashboard', DashboardController::class, 'index');

$router->crud('/tareas', TareaController::class);
$router->crud('/modulos', ElementoController::class);
$router->crud('/personas', PersonaController::class);
$router->crud('/usuarios', UsuarioController::class);

// Logs
$router->get('/logs', LogController::class, 'index');
$router->get('/logs/{id}/ver', LogController::class, 'ver');

// Notificaciones
$router->get('/notificaciones',              NotificacionController::class, 'index');
$router->get('/notificaciones/{id}/ver',     NotificacionController::class, 'ver');
$router->post('/notificaciones/{id}/leida',  NotificacionController::class, 'marcarLeida');

$router->crud('/grupos', GrupoController::class);
$router->crud('/parametros', ParametroController::class);
