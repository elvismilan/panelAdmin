<?php

namespace Core;

use Core\View;
use Core\Request;
use Core\Auth;
use Core\ActionLogger;
use Core\Permission;
use Core\ThemeResolver;
use Throwable;

class Controller
{
    protected ?Model $model = null;
    protected View $view;
    protected Request $request;
    protected ?ActionLogger $logger = null;
    protected ThemeResolver $themeResolver;
    protected array $pageAssets = [
        'css' => [],
        'js' => [],
    ];

    public function __construct() {

        $this->view = new View();
        $this->request = new Request();
        $this->themeResolver = new ThemeResolver();
        try {
            $this->logger = new ActionLogger();
        } catch (Throwable) {
            $this->logger = null;
        }
    }

    public function getModel(): ?Model {
        return $this->model;
    }

    public function getView(): View {

        return $this->view;
    }

    public function getRequest(): Request {

        return $this->request;
    }

    public function render(string $template, array $data = []): void {

        $assets = $this->pageAssets;
        if (isset($data['pageAssets']) && is_array($data['pageAssets'])) {
            $incomingCss = is_array($data['pageAssets']['css'] ?? null) ? $data['pageAssets']['css'] : [];
            $incomingJs = is_array($data['pageAssets']['js'] ?? null) ? $data['pageAssets']['js'] : [];

            $assets['css'] = array_values(array_unique(array_merge($assets['css'], $this->normalizeAssetList($incomingCss))));
            $assets['js'] = array_values(array_unique(array_merge($assets['js'], $this->normalizeAssetList($incomingJs))));
        }

        $data['pageAssets'] = $assets;

        $this->view->render($template, $data);
    }

    public function redirect(string $url): void {

        header('Location: ' . $url);
        exit;
    }

    public function json(array $data, int $statusCode = 200): void {

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function requireAuth(string $redirectTo = '/login'): void
    {
        Auth::requireAuth($redirectTo);
    }

    protected function requireElementPermission(int $elementId, string $redirectTo = '/dashboard'): void
    {
        $this->requireAuth();

        $user = Auth::user();
        $groupId = (string) ($user['group'] ?? '');

        if ($groupId === '') {
            $this->logAction('Acceso denegado sin grupo para elemento ' . $elementId, 'AUTHZ_DENY');
            $this->redirect($redirectTo);
        }

        $permission = new Permission();
        if ($permission->canAccessElement($groupId, $elementId)) {
            return;
        }

        $this->logAction('Acceso denegado al elemento ' . $elementId, 'AUTHZ_DENY');
        $this->redirect($redirectTo);
    }

    protected function logAction(string $action, string $type = 'GENERAL', bool $persist = true): array
    {
        $user = Auth::user();
        $payload = [
            'accion' => $action,
            'usuario' => (string) ($user['id'] ?? $user['username'] ?? 'ANON'),
            'fecha' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'tipo' => $type,
            'ip' => (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'),
            'pc' => (string) (@gethostbyaddr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0')) ?: 'unknown'),
        ];

        if (!$persist) {
            return $payload;
        }

        if ($this->logger === null) {
            error_log('[wr_logs_fallback] ' . json_encode($payload));
            return $payload;
        }

        $ok = $this->logger->record($payload);
        if (!$ok) {
            error_log('[wr_logs_fallback] ' . json_encode($payload));
        }

        return $payload;
    }

    protected function resolveTemplate(string $area, string $defaultView): string
    {
        return $this->themeResolver->resolve($area, $defaultView);
    }

    protected function setPageAssets(array $css = [], array $js = []): void
    {
        $this->pageAssets = [
            'css' => $this->normalizeAssetList($css),
            'js' => $this->normalizeAssetList($js),
        ];
    }

    protected function addCss(string $path): void
    {
        $path = trim($path);
        if ($path === '') {
            return;
        }

        if (!in_array($path, $this->pageAssets['css'], true)) {
            $this->pageAssets['css'][] = $path;
        }
    }

    protected function addJs(string $path): void
    {
        $path = trim($path);
        if ($path === '') {
            return;
        }

        if (!in_array($path, $this->pageAssets['js'], true)) {
            $this->pageAssets['js'][] = $path;
        }
    }

    protected function getPageAssets(): array
    {
        return $this->pageAssets;
    }

    private function normalizeAssetList(array $assets): array
    {
        $normalized = [];
        foreach ($assets as $asset) {
            if (!is_string($asset)) {
                continue;
            }

            $asset = trim($asset);
            if ($asset === '') {
                continue;
            }

            $normalized[] = $asset;
        }

        return array_values(array_unique($normalized));
    }
}