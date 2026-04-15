<?php

namespace Core;

class Permission
{
    private Database $db;
    private string $tablePermiso;
    private string $tableElemento;
    private string $tableGrupo;
    private string $tableTarea;

    public function __construct()
    {
        $dsn = $_ENV['DB_DSN'] ?? $_ENV['DB_NAME'] ?? '';
        if ($dsn === '') {
            throw new \RuntimeException('Missing DB_DSN/DB_NAME in environment configuration.');
        }

        if (strpos($dsn, ':') === false) {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $host, $dsn, $charset);
        }

        $prefix = (string) ($_ENV['DB_PREFIX'] ?? 'wr_');
        $this->tablePermiso = $prefix . 'permiso';
        $this->tableElemento = $prefix . 'elemento';
        $this->tableGrupo = $prefix . 'grupo';
        $this->tableTarea = $prefix . 'tarea';

        $this->db = Database::getInstance($dsn, (string) ($_ENV['DB_USER'] ?? ''), (string) ($_ENV['DB_PASS'] ?? ''));
    }

    public function canAccessElement(string $groupId, int $elementId): bool
    {
        $sql = "SELECT 1
                FROM {$this->tablePermiso} p
                INNER JOIN {$this->tableElemento} e ON e.ele_id = p.pmo_ele_id
                INNER JOIN {$this->tableGrupo} g ON g.gru_id = p.pmo_gru_id
                WHERE p.pmo_gru_id = :grupo
                  AND p.pmo_ele_id = :elemento
                  AND (e.ele_estado IS NULL OR e.ele_estado = 'H')
                  AND (g.gru_estado IS NULL OR g.gru_estado = 'H')
                LIMIT 1";

        $stmt = $this->db->query($sql, [
            'grupo' => $groupId,
            'elemento' => $elementId,
        ]);

        return (bool) $stmt->fetchColumn();
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

        foreach ($candidates as $candidate) {
            $sql = "SELECT ele_id
                    FROM {$this->tableElemento}
                    WHERE LOWER(TRIM(ele_nombre)) = LOWER(TRIM(:nombre))
                      AND (ele_estado IS NULL OR ele_estado = 'H')
                    LIMIT 1";

            $stmt = $this->db->query($sql, ['nombre' => $candidate]);
            $elementId = (int) $stmt->fetchColumn();
            if ($elementId > 0) {
                return $elementId;
            }
        }

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

        $placeholders = [];
        $params = [
            'grupo' => $groupId,
            'elemento' => $elementId,
        ];

        foreach ($taskCandidates as $index => $task) {
            $key = 'task_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $task;
        }

        $allowNullTask = in_array('ACCEDER', $taskCandidates, true) || in_array('LISTAR', $taskCandidates, true);

        $sql = "SELECT 1
                FROM {$this->tablePermiso} p
                INNER JOIN {$this->tableElemento} e ON e.ele_id = p.pmo_ele_id
                INNER JOIN {$this->tableGrupo} g ON g.gru_id = p.pmo_gru_id
                LEFT JOIN {$this->tableTarea} t ON t.tar_id = p.pmo_tar_id
                WHERE p.pmo_gru_id = :grupo
                  AND p.pmo_ele_id = :elemento
                  AND (e.ele_estado IS NULL OR e.ele_estado = 'H')
                  AND (g.gru_estado IS NULL OR g.gru_estado = 'H')
                  AND (
                        UPPER(TRIM(COALESCE(t.tar_nombre, ''))) IN (" . implode(', ', $placeholders) . ")";

        if ($allowNullTask) {
            $sql .= " OR p.pmo_tar_id IS NULL";
        }

        $sql .= ") LIMIT 1";

        $stmt = $this->db->query($sql, $params);
        return (bool) $stmt->fetchColumn();
    }
}