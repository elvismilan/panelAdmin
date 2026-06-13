<?php

namespace Core;

class Permission
{
    private static array $requestPermissionMatrix = [];
    private static array $requestElementMap = [];
    private static array $requestElementIdByPath = [];

    private Database $db;
    private string $tablePermiso;
    private string $tableElemento;
    private string $tableGrupo;
    private string $tableTarea;

    public function __construct()
    {
        RbacVersion::ensureFresh();
        $this->db = Database::fromEnv();
        $this->tablePermiso = TableNameResolver::resolve($this->db, 'permiso');
        $this->tableElemento = TableNameResolver::resolve($this->db, 'elemento');
        $this->tableGrupo = TableNameResolver::resolve($this->db, 'grupo');
        $this->tableTarea = TableNameResolver::resolve($this->db, 'tarea');
    }

    public function canAccessElement(string $groupId, int $elementId): bool
    {
        $groupId = trim($groupId);
        if ($groupId === '' || $elementId <= 0) {
            return false;
        }

        $matrix = $this->loadPermissionMatrix($groupId);
        return isset($matrix[$elementId]);
    }

    /**
     * Valida permiso por ruta y accion/controlador.
     *
     * Retorna:
     * - true: acceso permitido.
     * - false: modulo identificado pero accion sin permiso.
     * - null: la ruta no corresponde a un modulo controlado por permisos.
     */
    public function canAccessRoute(string $groupId, string $requestPath, string $action): ?bool
    {
        $groupId = trim($groupId);
        if ($groupId === '') {
            return false;
        }

        if ($this->isInternalBypassedPath($requestPath)) {
            return true;
        }

        $elementId = $this->resolveElementIdFromPath($requestPath);
        if ($elementId === null) {
            return null;
        }

        $tasks = $this->taskCandidates($action, $requestPath);
        return $this->canAccessElementTask($groupId, $elementId, $tasks);
    }

    private function resolveElementIdFromPath(string $requestPath): ?int
    {
        $normalizedPath = trim((string) parse_url($requestPath, PHP_URL_PATH));
        if ($normalizedPath === '' || $normalizedPath === '/') {
            return null;
        }

        $normalizedPath = '/' . trim($normalizedPath, '/');
        $cacheKey = strtolower($normalizedPath);
        if (array_key_exists($cacheKey, self::$requestElementIdByPath)) {
            return self::$requestElementIdByPath[$cacheKey];
        }

        $segments = array_values(array_filter(explode('/', trim($normalizedPath, '/')), static function (string $segment): bool {
            return $segment !== '' && !ctype_digit($segment);
        }));

        if ($segments === []) {
            return null;
        }

        $candidates = [];
        $full = implode('/', $segments);
        if ($full !== '') {
            $candidates[] = $full;
        }

        if ($full !== $segments[0]) {
            $candidates[] = $segments[0];
        }

        $candidates = array_values(array_unique($candidates));
        $elementMap = $this->loadActiveElementMap();

        foreach ($candidates as $candidate) {
            $lookup = strtolower(trim($candidate));
            $elementId = (int) ($elementMap[$lookup] ?? 0);
            if ($elementId > 0) {
                self::$requestElementIdByPath[$cacheKey] = $elementId;
                return $elementId;
            }
        }

        self::$requestElementIdByPath[$cacheKey] = null;
        return null;
    }

    /**
     * @return string[]
     */
    private function taskCandidates(string $action, string $requestPath): array
    {
        $normalizedAction = strtoupper(trim($action));
        $tasks = [];

        if ($normalizedAction !== '') {
            $tasks[] = $normalizedAction;
        }

        $map = [
            'INDEX' => ['ACCEDER', 'LISTAR'],
            'AGREGAR' => ['AGREGAR', 'CREAR', 'NUEVO'],
            'GUARDAR' => ['GUARDAR', 'CREAR', 'AGREGAR'],
            'EDITAR' => ['EDITAR'],
            'ACTUALIZAR' => ['ACTUALIZAR', 'EDITAR'],
            'ELIMINAR' => ['ELIMINAR'],
            'BORRAR' => ['BORRAR', 'ELIMINAR'],
            'SHOW' => ['VER', 'DETALLE'],
            'VER' => ['VER', 'DETALLE'],
            'DETALLE' => ['DETALLE', 'VER'],
        ];

        if (isset($map[$normalizedAction])) {
            $tasks = array_merge($tasks, $map[$normalizedAction]);
        }

        $segments = array_values(array_filter(explode('/', trim((string) parse_url($requestPath, PHP_URL_PATH), '/')), static function (string $segment): bool {
            return $segment !== '' && !ctype_digit($segment);
        }));

        if (count($segments) === 1) {
            $tasks[] = 'ACCEDER';
            $tasks[] = 'LISTAR';
        } elseif ($segments !== []) {
            $tasks[] = strtoupper((string) end($segments));
        }

        return array_values(array_unique(array_filter(array_map(static function (string $value): string {
            return strtoupper(trim($value));
        }, $tasks), static function (string $value): bool {
            return $value !== '';
        })));
    }

    /**
     * @param string[] $taskCandidates
     */
    private function canAccessElementTask(string $groupId, int $elementId, array $taskCandidates): bool
    {
        $taskCandidates = array_values(array_unique(array_filter(array_map(static function (string $task): string {
            return strtoupper(trim($task));
        }, $taskCandidates), static function (string $task): bool {
            return $task !== '';
        })));

        if ($taskCandidates === []) {
            return false;
        }

        $matrix = $this->loadPermissionMatrix($groupId);
        $elementPermissions = $matrix[$elementId] ?? null;
        if (!is_array($elementPermissions)) {
            return false;
        }

        $allowNullTask = in_array('ACCEDER', $taskCandidates, true) || in_array('LISTAR', $taskCandidates, true);
        if ($allowNullTask && !empty($elementPermissions['allow_null_task'])) {
            return true;
        }

        $tasks = is_array($elementPermissions['tasks'] ?? null)
            ? $elementPermissions['tasks']
            : [];

        foreach ($taskCandidates as $taskCandidate) {
            if (isset($tasks[$taskCandidate])) {
                return true;
            }
        }

        return false;
    }

    private function isInternalBypassedPath(string $requestPath): bool
    {
        $path = trim((string) parse_url($requestPath, PHP_URL_PATH));
        if ($path === '') {
            return false;
        }

        return str_starts_with('/' . trim($path, '/'), '/notificaciones');
    }

    private function loadPermissionMatrix(string $groupId): array
    {
        if (isset(self::$requestPermissionMatrix[$groupId])) {
            return self::$requestPermissionMatrix[$groupId];
        }

        Session::start();
        $cached = Session::get(RbacCache::PERMISSION_MATRIX_KEY);
        if (is_array($cached) && ($cached['group'] ?? '') === $groupId && is_array($cached['matrix'] ?? null)) {
            self::$requestPermissionMatrix[$groupId] = $cached['matrix'];
            return $cached['matrix'];
        }

        $matrix = $this->buildPermissionMatrix($groupId);
        Session::set(RbacCache::PERMISSION_MATRIX_KEY, [
            'group' => $groupId,
            'matrix' => $matrix,
        ]);
        self::$requestPermissionMatrix[$groupId] = $matrix;

        return $matrix;
    }

    private function buildPermissionMatrix(string $groupId): array
    {
        $sql = "SELECT p.pmo_ele_id, p.pmo_tar_id, t.tar_nombre
                FROM {$this->tablePermiso} p
                INNER JOIN {$this->tableElemento} e ON e.ele_id = p.pmo_ele_id
                INNER JOIN {$this->tableGrupo} g ON g.gru_id = p.pmo_gru_id
                LEFT JOIN {$this->tableTarea} t ON t.tar_id = p.pmo_tar_id
                WHERE p.pmo_gru_id = :grupo
                  AND (e.ele_estado IS NULL OR e.ele_estado = 'H')
                  AND (g.gru_estado IS NULL OR g.gru_estado = 'H')";

        $rows = $this->db->query($sql, ['grupo' => $groupId])->fetchAll();
        $matrix = [];

        foreach ($rows as $row) {
            $elementId = (int) ($row['pmo_ele_id'] ?? 0);
            if ($elementId <= 0) {
                continue;
            }

            if (!isset($matrix[$elementId])) {
                $matrix[$elementId] = [
                    'allow_null_task' => false,
                    'tasks' => [],
                ];
            }

            $taskId = $row['pmo_tar_id'] ?? null;
            if ($taskId === null || $taskId === '') {
                $matrix[$elementId]['allow_null_task'] = true;
                continue;
            }

            $taskName = strtoupper(trim((string) ($row['tar_nombre'] ?? '')));
            if ($taskName !== '') {
                $matrix[$elementId]['tasks'][$taskName] = true;
            }
        }

        return $matrix;
    }

    private function loadActiveElementMap(): array
    {
        if (self::$requestElementMap !== []) {
            return self::$requestElementMap;
        }

        Session::start();
        $cached = Session::get(RbacCache::ELEMENT_MAP_KEY);
        if (is_array($cached) && is_array($cached['map'] ?? null)) {
            self::$requestElementMap = $cached['map'];
            return $cached['map'];
        }

        $rows = $this->db->query(
            "SELECT ele_id, ele_nombre
             FROM {$this->tableElemento}
             WHERE ele_nombre IS NOT NULL
               AND (ele_estado IS NULL OR ele_estado = 'H')"
        )->fetchAll();

        $map = [];
        foreach ($rows as $row) {
            $name = strtolower(trim((string) ($row['ele_nombre'] ?? '')));
            $id = (int) ($row['ele_id'] ?? 0);

            if ($name === '' || $id <= 0 || isset($map[$name])) {
                continue;
            }

            $map[$name] = $id;
        }

        Session::set(RbacCache::ELEMENT_MAP_KEY, ['map' => $map]);
        self::$requestElementMap = $map;

        return $map;
    }
}
