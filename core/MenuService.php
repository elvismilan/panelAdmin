<?php

namespace Core;

class MenuService
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

    public function buildForGroup(string $groupId): array
    {
        $groupId = trim($groupId);
        if ($groupId === '') {
            return [];
        }

        $sql = "SELECT DISTINCT
                    e.ele_id,
                    e.ele_padre,
                    e.ele_nombre,
                    e.ele_titulo,
                    e.ele_tipo,
                    e.ele_tarea,
                    e.ele_icono,
                    e.ele_orden,
                    t.tar_nombre
                FROM {$this->tableElemento} e
                INNER JOIN {$this->tablePermiso} p ON p.pmo_ele_id = e.ele_id
                INNER JOIN {$this->tableGrupo} g ON g.gru_id = p.pmo_gru_id
                LEFT JOIN {$this->tableTarea} t ON t.tar_id = p.pmo_tar_id
                WHERE p.pmo_gru_id = :grupo
                  AND (e.ele_estado IS NULL OR e.ele_estado = 'H')
                  AND (g.gru_estado IS NULL OR g.gru_estado = 'H')
                ORDER BY e.ele_orden ASC, e.ele_id ASC";

        $rows = $this->db->query($sql, ['grupo' => $groupId])->fetchAll();
        if (!is_array($rows) || $rows === []) {
            return [];
        }

        $itemsById = [];

        foreach ($rows as $row) {
            $id = (int) ($row['ele_id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            if (!isset($itemsById[$id])) {
                $itemsById[$id] = [
                    'id' => $id,
                    'parent_id' => max(0, (int) ($row['ele_padre'] ?? 0)),
                    'name' => trim((string) ($row['ele_nombre'] ?? '')),
                    'title' => trim((string) ($row['ele_titulo'] ?? '')),
                    'type' => strtoupper(trim((string) ($row['ele_tipo'] ?? 'L'))),
                    'icon' => trim((string) ($row['ele_icono'] ?? '')),
                    'order' => (int) ($row['ele_orden'] ?? 0),
                    'default_task' => trim((string) ($row['ele_tarea'] ?? 'ACCEDER')),
                    'tasks' => [],
                    'children' => [],
                    'url' => '/dashboard',
                ];
            }

            $taskName = trim((string) ($row['tar_nombre'] ?? ''));
            if ($taskName !== '') {
                $itemsById[$id]['tasks'][strtoupper($taskName)] = $taskName;
            }
        }

        foreach ($itemsById as $id => $item) {
            $task = $this->pickTask($item['tasks'], $item['default_task']);
            $itemsById[$id]['url'] = $this->buildUrl($item['name'], $task);
        }

        $tree = [];
        foreach ($itemsById as $id => $item) {
            $parentId = (int) $item['parent_id'];
            if ($parentId > 0 && isset($itemsById[$parentId])) {
                $itemsById[$parentId]['children'][] = $id;
                continue;
            }

            $tree[] = $id;
        }

        $sortTree = function (array $ids) use (&$sortTree, &$itemsById): array {
            usort($ids, function (int $a, int $b) use ($itemsById): int {
                $left = $itemsById[$a] ?? null;
                $right = $itemsById[$b] ?? null;
                if ($left === null || $right === null) {
                    return $a <=> $b;
                }

                $cmp = ((int) $left['order']) <=> ((int) $right['order']);
                if ($cmp !== 0) {
                    return $cmp;
                }

                return strcasecmp((string) $left['title'], (string) $right['title']);
            });

            foreach ($ids as $id) {
                $itemsById[$id]['children'] = $sortTree($itemsById[$id]['children']);
            }

            return $ids;
        };

        $tree = $sortTree($tree);

        return $this->exportTree($tree, $itemsById);
    }

    private function exportTree(array $ids, array $itemsById): array
    {
        $result = [];
        foreach ($ids as $id) {
            if (!isset($itemsById[$id])) {
                continue;
            }

            $item = $itemsById[$id];
            $title = $item['title'] !== '' ? $item['title'] : $item['name'];
            $children = $this->exportTree($item['children'], $itemsById);

            $result[] = [
                'id' => $item['id'],
                'title' => $title,
                'url' => $item['url'],
                'icon' => $item['icon'],
                'type' => $item['type'],
                'children' => $children,
            ];
        }

        return $result;
    }

    private function pickTask(array $tasks, string $defaultTask): string
    {
        if (isset($tasks['ACCEDER'])) {
            return 'ACCEDER';
        }

        $taskValues = array_values($tasks);
        if (!empty($taskValues)) {
            return (string) $taskValues[0];
        }

        $fallback = trim($defaultTask);
        return $fallback !== '' ? $fallback : 'ACCEDER';
    }

    private function buildUrl(string $elementName, string $task): string
    {
        $path = trim($elementName);
        if ($path === '') {
            return '/dashboard';
        }

        $normalizedPath = implode('/', array_filter(array_map('trim', explode('/', trim($path, '/'))), static function ($part): bool {
            return $part !== '';
        }));

        if ($normalizedPath === '') {
            return '/dashboard';
        }

        $url = '/' . $normalizedPath;
        if (strcasecmp(trim($task), 'ACCEDER') === 0) {
            return $url;
        }

        $taskPart = strtolower(trim($task));
        if ($taskPart === '') {
            return $url;
        }

        return $url . '/' . $taskPart;
    }
}
