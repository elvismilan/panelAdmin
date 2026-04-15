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

        if (!isset($data['flashes'])) {
            $data['flashes'] = $this->consumeFlashes();
        }

        if (!isset($data['adminMenu']) && Auth::check()) {
            $user = Auth::user();
            $groupId = trim((string) ($user['group'] ?? ''));

            if ($groupId !== '') {
                try {
                    $menuService = new MenuService();
                    $data['adminMenu'] = $menuService->buildForGroup($groupId);
                } catch (Throwable) {
                    $data['adminMenu'] = [];
                }
            } else {
                $data['adminMenu'] = [];
            }
        }

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
        $ip   = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $payload = [
            'accion'  => $action,
            'usuario' => (string) ($user['id'] ?? $user['username'] ?? 'ANON'),
            'fecha'   => date('Y-m-d'),
            'hora'    => date('H:i:s'),
            'tipo'    => $type,
            'ip'      => $ip,
            'pc'      => $this->resolveHostname($ip),
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

    private function resolveHostname(string $ip): string
    {
        $cacheKey = '_hostname_cache';

        Session::start();
        $cache = Session::get($cacheKey, []);

        if (!is_array($cache)) {
            $cache = [];
        }

        if (isset($cache[$ip])) {
            return $cache[$ip];
        }

        $hostname = gethostbyaddr($ip);
        $resolved = ($hostname !== false && $hostname !== $ip) ? $hostname : 'unknown';

        $cache[$ip] = $resolved;
        Session::set($cacheKey, $cache);

        return $resolved;
    }

    protected function renderAdminModule(string $moduleView, array $data = []): void
    {
        $data['moduleView'] = $moduleView;
        $this->render($this->resolveTemplate('admin', 'layout'), $data);
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

    protected function getDefaultPerPage(): int
    {
        $value = (int) ($_ENV['PAGINATION_PER_PAGE'] ?? 8);
        if ($value < 1) {
            return 8;
        }

        return min($value, 100);
    }

    protected function getCurrentPage(): int
    {
        $params = $this->request->getParams();
        $page = (int) ($params['page'] ?? 1);

        return max($page, 1);
    }

    protected function getQueryParam(string $key, string $default = ''): string
    {
        $params = $this->request->getParams();
        $value = $params[$key] ?? $default;

        return is_string($value) ? trim($value) : $default;
    }

    protected function getQueryParams(array $fields): array
    {
        $params = [];

        foreach ($fields as $key => $default) {
            if (is_int($key)) {
                $name = (string) $default;
                $fallback = '';
            } else {
                $name = (string) $key;
                $fallback = is_scalar($default) ? (string) $default : '';
            }

            if ($name === '') {
                continue;
            }

            $params[$name] = $this->getQueryParam($name, $fallback);
        }

        return $params;
    }

    protected function buildPagination(int $totalRows, int $currentPage, int $perPage, string $basePath, array $queryParams = []): array
    {
        $safePerPage = max(1, $perPage);
        $totalPages = max((int) ceil($totalRows / $safePerPage), 1);
        $safePage = max(1, min($currentPage, $totalPages));
        $offset = ($safePage - 1) * $safePerPage;
        $from = $totalRows > 0 ? $offset + 1 : 0;
        $to = min($offset + $safePerPage, $totalRows);

        $cleanQuery = [];
        foreach ($queryParams as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if (!is_scalar($value)) {
                continue;
            }

            $stringValue = trim((string) $value);
            if ($stringValue === '') {
                continue;
            }

            $cleanQuery[$key] = $stringValue;
        }

        return [
            'totalRows' => $totalRows,
            'perPage' => $safePerPage,
            'currentPage' => $safePage,
            'totalPages' => $totalPages,
            'offset' => $offset,
            'from' => $from,
            'to' => $to,
            'hasPrev' => $safePage > 1,
            'hasNext' => $safePage < $totalPages,
            'prevPage' => $safePage - 1,
            'nextPage' => $safePage + 1,
            'basePath' => $basePath,
            'query' => $cleanQuery,
        ];
    }

    protected function flash(string $type, string $message): void
    {
        $type = trim($type);
        $message = trim($message);
        if ($type === '' || $message === '') {
            return;
        }

        Session::start();
        $flashes = Session::get('_flash_messages', []);
        if (!is_array($flashes)) {
            $flashes = [];
        }

        if (!isset($flashes[$type]) || !is_array($flashes[$type])) {
            $flashes[$type] = [];
        }

        $flashes[$type][] = $message;
        Session::set('_flash_messages', $flashes);
    }

    protected function flashSuccess(string $message): void
    {
        $this->flash('success', $message);
    }

    protected function flashError(string $message): void
    {
        $this->flash('danger', $message);
    }

    protected function consumeFlashes(): array
    {
        Session::start();
        $flashes = Session::get('_flash_messages', []);
        Session::remove('_flash_messages');

        return is_array($flashes) ? $flashes : [];
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