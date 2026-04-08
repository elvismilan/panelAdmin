<?php

namespace Core;

use Core\Template;

class View
{
    private string $templatePath;

    public function __construct(?string $templatePath = null)
    {
        $this->templatePath = $templatePath ?: dirname(__DIR__) . '/app/views';
    }

    public function render(string $templateFile, array $data = []): void
    {
        $template = new Template($this->templatePath, $templateFile);
        echo $template->render($data);
    }

    public function setTemplatePath(string $templatePath): void
    {
        $this->templatePath = $templatePath;
    }
}