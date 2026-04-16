<?php

namespace Core;

use Core\Template;

class View
{
    private string $templatePath;
    private string $resourcesPath;

    public function __construct(?string $templatePath = null)
    {
        $this->templatePath = $templatePath ?: dirname(__DIR__) . '/app/Views';
        $this->resourcesPath = dirname(__DIR__) . '/resources';
    }

    public function render(string $templateFile, array $data = []): void
    {
        $basePath = $this->templatePath;

        if (str_starts_with($templateFile, 'resources:')) {
            $templateFile = ltrim(substr($templateFile, strlen('resources:')), '/');
            $basePath = $this->resourcesPath;
        }

        $template = new Template($basePath, $templateFile);
        echo $template->render($data);
    }

    public function setTemplatePath(string $templatePath): void
    {
        $this->templatePath = $templatePath;
    }
}