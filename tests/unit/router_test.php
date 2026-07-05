<?php

declare(strict_types=1);

use Core\Router;

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
    $router->crud('/usuarios', TestRouterController::class);

    $routes = $router->getRoutes();

    assert_same(7, count($routes));
    assert_same('/usuarios', $routes[0]['path']);
    assert_same('/usuarios/{id}/editar', $routes[3]['path']);
    assert_same('guardar', $routes[2]['action']);
});

test('Router dispatch resuelve parametros dinamicos', function (): void {
    TestRouterController::$calls = [];

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/usuarios/15/editar';
    $_SERVER['SCRIPT_NAME'] = '/index.php';

    $router = new Router();
    $router->get('/usuarios/{id}/editar', TestRouterController::class, 'editar');
    $router->dispatch();

    assert_same([['action' => 'editar', 'id' => '15']], TestRouterController::$calls);
});
