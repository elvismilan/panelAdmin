<?php

namespace Core;

use Throwable;

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
                if ($this->isForbidden($path, (string) ($route['action'] ?? ''))) {
                    $this->renderErrorPage(403, 'resources:themes/default/errors/forbidden', [
                        'title' => 'Acceso denegado',
                        'code' => 403,
                        'heading' => 'No tienes permiso para esta accion',
                        'message' => 'Tu grupo no tiene habilitada esta tarea para el modulo solicitado.',
                        'homeUrl' => '/dashboard',
                    ]);
                    return;
                }

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

        $this->renderErrorPage(404, 'resources:themes/default/errors/not-found', [
            'title' => 'Pagina no encontrada',
            'code' => 404,
            'heading' => 'Modulo o ruta no encontrada',
            'message' => 'La URL solicitada no existe o fue movida.',
            'homeUrl' => Auth::check() ? '/dashboard' : '/',
        ]);
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

    private function isForbidden(string $path, string $action): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $normalizedPath = trim($path);
        if ($normalizedPath === '' || $normalizedPath === '/' || str_starts_with($normalizedPath, '/dashboard')) {
            return false;
        }

        $user = Auth::user();
        $groupId = trim((string) ($user['group'] ?? ''));
        if ($groupId === '') {
            return true;
        }

        try {
            $permission = new Permission();
            $result = $permission->canAccessRoute($groupId, $normalizedPath, $action);
            return $result === false;
        } catch (Throwable $e) {
            error_log('[Router::isForbidden] Permission check failed: ' . $e->getMessage());
            return false;
        }
    }

    private function renderErrorPage(int $statusCode, string $template, array $data = []): void
    {
        http_response_code($statusCode);

        try {
            $view = new View();
            $view->render($template, $data);
            return;
        } catch (Throwable) {
            // Fallback defensivo si falla el template o assets.
        }

        echo $statusCode === 403 ? '403 Forbidden' : '404 Not Found';
    }

    public function getRoutes(): array {

        return $this->routes;
    }

    public function setRoutes(array $routes): void {

        $this->routes = $routes;
    }
}