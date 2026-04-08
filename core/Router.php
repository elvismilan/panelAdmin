<?php

namespace Core;

class Router {

    private array $routes;

    public function __construct() {

        $this->routes = [];
    }

    public function addRoute(string $method, string $path, string $controller, string $action): void {

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
        ];
    }

    public function get(string $path, string $controller, string $action): void
    {
        $this->addRoute('GET', $path, $controller, $action);
    }

    public function post(string $path, string $controller, string $action): void
    {
        $this->addRoute('POST', $path, $controller, $action);
    }

    public function put(string $path, string $controller, string $action): void
    {
        $this->addRoute('PUT', $path, $controller, $action);
    }

    public function delete(string $path, string $controller, string $action): void
    {
        $this->addRoute('DELETE', $path, $controller, $action);
    }

    public function dispatch(): void {

        $request = new Request();
        $method = $request->getMethod();
        $path = $request->getPath();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $matches = [];
            if ($this->matchPath($route['path'], $path, $matches)) {
                $controllerClass = $route['controller'];
                $action = $route['action'];

                if (!class_exists($controllerClass)) {
                    http_response_code(500);
                    echo 'Controller not found: ' . $controllerClass;
                    return;
                }

                $controller = new $controllerClass();
                if (!method_exists($controller, $action)) {
                    http_response_code(500);
                    echo 'Action not found: ' . $action;
                    return;
                }

                call_user_func_array([$controller, $action], $matches);
                return;
            }
        }

        http_response_code(404);
        echo '404 Not Found';
    }

    public function route(): void
    {
        $this->dispatch();
    }

    private function matchPath(string $routePath, string $requestPath, array &$params): bool
    {
        $params = [];

        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $requestPath, $matches)) {
            return false;
        }

        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[] = $value;
            }
        }

        return true;
    }

    public function getRoutes(): array {

        return $this->routes;
    }

    public function setRoutes(array $routes): void {

        $this->routes = $routes;
    }
}