<?php

declare(strict_types=1);

use Core\Request;

test('Request normaliza metodo path y params basicos', function (): void {
    $_ENV['APP_URL'] = 'http://localhost/panel';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/panel/usuarios?q=demo';
    $_SERVER['SCRIPT_NAME'] = '/panel/public/index.php';
    $_GET = ['q' => 'demo'];
    $_POST = [];

    $request = new Request();

    assert_same('GET', $request->getMethod());
    assert_same('/usuarios', $request->getPath());
    assert_same(['q' => 'demo'], $request->getQueryParams());
    assert_same(['q' => 'demo'], $request->getParams());
    assert_true($request->isGet());
});

test('Request respeta override _method y remueve /index.php', function (): void {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/index.php/tareas/15';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_POST = ['_method' => 'delete', 'csrf_token' => 'abc'];
    $_GET = [];

    $request = new Request();

    assert_same('DELETE', $request->getMethod());
    assert_same('/tareas/15', $request->getPath());
    assert_true($request->isDelete());
    assert_array_has_key('csrf_token', $request->getPostParams());
});

test('Request devuelve headers desde $_SERVER', function (): void {
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    $request = new Request();

    assert_same('XMLHttpRequest', $request->getHeader('X-Requested-With'));
    assert_null($request->getHeader('X-Missing'));
});
