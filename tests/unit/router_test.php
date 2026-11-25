<?php

declare(strict_types=1);

use PanelAdmin\Core\Router;

final class TestRouterController
{
    public static array $calls = [];

    public function editar(string $id): void
    {
        self::$calls[] = ['action' => 'editar', 'id' => $id];
    }
}

test('Router crud registra las rutas esperadas', function (): void {
    $router = new Router();
    $router->crud('/pruebas', TestRouterController::class);

    $routes = $router->getRoutes();

    assert_same(7, count($routes));
    assert_same('/pruebas', $routes[0]['path']);
    assert_same('/pruebas/{id}/editar', $routes[3]['path']);
    assert_same('guardar', $routes[2]['action']);
});

test('Router dispatch resuelve parametros dinamicos', function (): void {
    TestRouterController::$calls = [];

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/pruebas/15/editar';
    $_SERVER['SCRIPT_NAME'] = '/index.php';

    $router = new Router();
    $router->get('/pruebas/{id}/editar', TestRouterController::class, 'editar');
    $router->dispatch();

    assert_same([['action' => 'editar', 'id' => '15']], TestRouterController::$calls);
});

test('Router atiende HEAD con la ruta GET', function (): void {
    TestRouterController::$calls = [];

    $_SERVER['REQUEST_METHOD'] = 'HEAD';
    $_SERVER['REQUEST_URI'] = '/pruebas/7/editar';
    $_SERVER['SCRIPT_NAME'] = '/index.php';

    $router = new Router();
    $router->get('/pruebas/{id}/editar', TestRouterController::class, 'editar');
    ob_start();
    $router->dispatch();
    ob_end_clean();

    assert_same([['action' => 'editar', 'id' => '7']], TestRouterController::$calls);
});

test('Router no atiende HEAD con rutas POST', function (): void {
    TestRouterController::$calls = [];

    $_SERVER['REQUEST_METHOD'] = 'HEAD';
    $_SERVER['REQUEST_URI'] = '/pruebas/7/editar';
    $_SERVER['SCRIPT_NAME'] = '/index.php';

    $router = new Router();
    $router->post('/pruebas/{id}/editar', TestRouterController::class, 'editar');
    ob_start();
    $router->dispatch();
    ob_end_clean();

    assert_same([], TestRouterController::$calls);
    assert_same(404, http_response_code());
});
