<?php

namespace Core;

class Request
{
    private string $method;
    private string $path;
    private array $query;
    private array $post;
    private array $params;
    private array $body;

    public function __construct()
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper((string) $_POST['_method']);
        }

        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $basePath = Url::basePath();
        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = (string) substr($path, strlen($basePath));
            if ($path === '') {
                $path = '/';
            }
        }

        if (str_starts_with($path, '/index.php/')) {
            $path = (string) substr($path, strlen('/index.php'));
        } elseif ($path === '/index.php') {
            $path = '/';
        }

        $this->method = $method;
        $this->path = $path;
        $this->query = $_GET;
        $this->post  = $_POST;
        // Unifica solo query + post. Evita $_REQUEST para no mezclar cookies.
        $this->params = array_merge($this->query, $this->post);

        $rawBody = (string) file_get_contents('php://input');
        $decoded = json_decode($rawBody, true);
        $this->body = is_array($decoded) ? $decoded : [];
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getQueryParams(): array
    {
        return $this->query;
    }

    public function getPostParams(): array
    {
        return $this->post;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getHeader(string $header): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        return $_SERVER[$key] ?? null;
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isPut(): bool
    {
        return $this->method === 'PUT';
    }

    public function isDelete(): bool
    {
        return $this->method === 'DELETE';
    }
}
