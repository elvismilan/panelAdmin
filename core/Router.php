<?php

namespace Core;

class Router {
    private $routes = [];

    public function add($route, $params = []) {
        $route = preg_replace('/\//', '\\/', $route);
        $route = '/^' . $route . '$/i';
        $this->routes[$route] = $params;
    }

    public function getRoutes() {
        return $this->routes;
    }

    public function match($url) {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }
                return $params;
            }
        }
        return false;
    }

    public function dispatch($url) {
        $url = $this->removeQueryStringVariables($url);
        $url = rtrim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);

        if ($params = $this->match($url)) {
            $controllerName = 'App\\Controllers\\' . ucfirst($params['controller']) . 'Controller';
            $methodName = $params['action'];

            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                if (method_exists($controller, $methodName)) {
                    call_user_func_array([$controller, $methodName], []);
                } else {
                    echo "Method $methodName not found in $controllerName";
                }
            } else {
                echo "Controller $controllerName not found";
            }
        } else {
            echo "No route matched.";
        }
    }

    protected function removeQueryStringVariables($url) {
        if ($url != '') {
            $parts = explode('&', $url, 2);
            if (strpos($parts[0], '=') === false) {
                $url = $parts[0];
            } else {
                $url = '';
            }
        }
        return $url;
    }
}
