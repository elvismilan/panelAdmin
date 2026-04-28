<?php

namespace Core;

class Template {
    
    private string $templatePath;
    private string $templateFile;

    public function __construct(string $templatePath, string $templateFile) {

        $this->templatePath = $templatePath;
        $this->templateFile = $templateFile;
    }

    public function render(array $data = []): string {

        $template = $this->templatePath . '/' . $this->templateFile . '.php';
        if (!is_file($template)) {
            throw new \RuntimeException('Template not found: ' . $template);
        }

        extract($data);
        ob_start();
        include $template;
        $output = (string) ob_get_clean();

        return Url::absolutizeHtmlAttributes($output);
    }

    public function setTemplatePath(string $templatePath): void {

        $this->templatePath = $templatePath;
    }

    public function setTemplateFile(string $templateFile): void {

        $this->templateFile = $templateFile;

    }

    public function getTemplatePath(): string {

        return $this->templatePath;
    }

    public function getTemplateFile(): string {
        
        return $this->templateFile;
    }
}