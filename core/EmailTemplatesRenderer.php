<?php

namespace Core;

class EmailTemplatesRenderer
{
    private string $viewsBasePath;

    public function __construct(?string $viewsBasePath = null)
    {
        $this->viewsBasePath = $viewsBasePath ?? dirname(__DIR__) . '/app/Views';
    }

    public function render(string $viewPath, array $data = []): string
    {
        $normalizedView = trim(str_replace('\\', '/', $viewPath), '/');
        if ($normalizedView === '' || str_contains($normalizedView, '..')) {
            throw new \RuntimeException('Invalid email view path.');
        }

        $fullPath = $this->viewsBasePath . '/' . $normalizedView . '.php';
        if (!is_file($fullPath)) {
            throw new \RuntimeException('Email view not found: ' . $fullPath);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $fullPath;
        return (string) ob_get_clean();
    }
}
