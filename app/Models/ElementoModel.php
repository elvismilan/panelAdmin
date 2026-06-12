<?php

namespace App\Models;

use Core\Model;
use Throwable;

class ElementoModel extends Model
{
    private string $elementoTable;
    private string $elementoTareaTable;
    private string $tareaTable;

    public function __construct()
    {
        parent::__construct();
        $this->elementoTable      = $this->tableName('elemento');
        $this->elementoTareaTable = $this->tableName('elemento_tarea');
        $this->tareaTable         = $this->tableName('tarea');
        $this->setTable('elemento');
        $this->setPrimaryKey('ele_id');
    }

    public function paginate(int $offset, int $limit, string $search = ''): array
    {
        $sql = "SELECT ele_id, ele_nombre, ele_titulo, ele_tipo, ele_estado, ele_orden, ele_padre
                FROM {$this->elementoTable}";
        if ($search !== '') {
            $sql .= " WHERE ele_nombre LIKE :search1 OR ele_titulo LIKE :search2";
        }
        $sql .= " ORDER BY ele_orden ASC, ele_id ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        if ($search !== '') {
            $pattern = $this->likePattern($search);
            $stmt->bindValue(':search1', $pattern, \PDO::PARAM_STR);
            $stmt->bindValue(':search2', $pattern, \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function paginateWithParentFilter(int $offset, int $limit, string $search = '', string $padre = ''): array
    {
        $sql = "SELECT ele_id, ele_nombre, ele_titulo, ele_tipo, ele_estado, ele_orden, ele_padre
                FROM {$this->elementoTable}";
        
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = "(ele_nombre LIKE :search1 OR ele_titulo LIKE :search2)";
            $pattern = $this->likePattern($search);
            $params['search1'] = $pattern;
            $params['search2'] = $pattern;
        }

        if ($padre !== '') {
            $padreInt = (int) $padre;
            if ($padreInt === 0) {
                $conditions[] = "(ele_padre IS NULL OR ele_padre = 0)";
            } else {
                $conditions[] = "ele_padre = :padre";
                $params['padre'] = $padreInt;
            }
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY ele_orden ASC, ele_id ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getPadreFilterOptions(): array
    {
        // Mostrar solo padres reales con hijos visibles.
        // "Inicio" no debe aparecer como opcion del filtro.
        $sql = "SELECT 
                    p.ele_id AS value,
                    p.ele_titulo AS label,
                    COUNT(h.ele_id) AS count
                FROM {$this->elementoTable} p
                INNER JOIN {$this->elementoTable} h ON h.ele_padre = p.ele_id
                WHERE (p.ele_padre IS NULL OR p.ele_padre = 0)
                  AND LOWER(TRIM(COALESCE(p.ele_titulo, ''))) <> 'inicio'
                GROUP BY p.ele_id, p.ele_titulo
                ORDER BY p.ele_titulo ASC";
        
        return $this->db->query($sql)->fetchAll();
    }

    public function countAll(string $search = ''): int
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->elementoTable}";
        $params = [];

        if ($search !== '') {
            $sql .= " WHERE ele_nombre LIKE :search1 OR ele_titulo LIKE :search2";
            $pattern = $this->likePattern($search);
            $params['search1'] = $pattern;
            $params['search2'] = $pattern;
        }

        $row = $this->db->query($sql, $params)->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function countAllWithFilters(string $search = '', string $estado = '', string $tipo = ''): int
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->elementoTable}";
        $params = [];
        $conditions = [];

        if ($search !== '') {
            $conditions[] = "(ele_nombre LIKE :search1 OR ele_titulo LIKE :search2)";
            $pattern = $this->likePattern($search);
            $params['search1'] = $pattern;
            $params['search2'] = $pattern;
        }

        if ($estado !== '') {
            $conditions[] = "ele_estado = :estado";
            $params['estado'] = $estado;
        }

        if ($tipo !== '') {
            $conditions[] = "ele_tipo = :tipo";
            $params['tipo'] = $tipo;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $row = $this->db->query($sql, $params)->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function countAllWithParentFilter(string $search = '', string $padre = ''): int
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->elementoTable}";
        $params = [];
        $conditions = [];

        if ($search !== '') {
            $conditions[] = "(ele_nombre LIKE :search1 OR ele_titulo LIKE :search2)";
            $pattern = $this->likePattern($search);
            $params['search1'] = $pattern;
            $params['search2'] = $pattern;
        }

        if ($padre !== '') {
            $padreInt = (int) $padre;
            if ($padreInt === 0) {
                $conditions[] = "(ele_padre IS NULL OR ele_padre = 0)";
            } else {
                $conditions[] = "ele_padre = :padre";
                $params['padre'] = $padreInt;
            }
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $row = $this->db->query($sql, $params)->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function findById(string $id): ?array
    {
        $sql = "SELECT ele_id, ele_nombre, ele_titulo, ele_tipo, ele_estado,
                       ele_icono, ele_orden, ele_padre, ele_tarea
                FROM {$this->elementoTable}
                WHERE ele_id = :id LIMIT 1";
        $row = $this->db->query($sql, ['id' => $id])->fetch();
        return is_array($row) ? $row : null;
    }

    /** All elements for dropdowns (padre selector). */
    public function allForDropdown(): array
    {
        $sql = "SELECT ele_id, ele_titulo, ele_padre
                FROM {$this->elementoTable}
                WHERE ele_padre IS NULL OR ele_padre = 0
                ORDER BY ele_titulo ASC, ele_id ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /** All available tareas. */
    public function allTareas(): array
    {
        $sql = "SELECT tar_id, tar_nombre FROM {$this->tareaTable} ORDER BY tar_nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /** Tarea IDs linked to a given elemento. */
    public function getElementoTareaIds(string $eleId): array
    {
        $sql = "SELECT eta_tar_id FROM {$this->elementoTareaTable} WHERE eta_ele_id = :ele_id";
        $rows = $this->db->query($sql, ['ele_id' => $eleId])->fetchAll();
        return array_column($rows, 'eta_tar_id');
    }

    public function createElemento(array $data, array $tareaIds = []): string
    {
        $this->db->beginTransaction();

        try {
            $eleId = $this->create([
                'ele_nombre' => (string) ($data['ele_nombre'] ?? ''),
                'ele_titulo'  => (string) ($data['ele_titulo'] ?? ''),
                'ele_estado'  => (string) ($data['ele_estado'] ?? 'H'),
                'ele_icono'   => ($data['ele_icono'] ?? '') !== '' ? (string) $data['ele_icono'] : null,
                'ele_orden'   => ($data['ele_orden'] ?? '') !== '' ? (int) $data['ele_orden'] : null,
                'ele_tipo'    => (string) ($data['ele_tipo'] ?? 'M'),
                'ele_padre'   => ($data['ele_padre'] ?? '') !== '' ? (int) $data['ele_padre'] : null,
                'ele_tarea'   => (string) ($data['ele_tarea'] ?? 'ACCEDER'),
            ]);

            $this->syncTareas($eleId, $tareaIds);
            $this->db->commit();

            return $eleId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateElemento(string $id, array $data, array $tareaIds = []): bool
    {
        $this->db->beginTransaction();

        try {
            $this->update($id, [
                'ele_nombre' => (string) ($data['ele_nombre'] ?? ''),
                'ele_titulo'  => (string) ($data['ele_titulo'] ?? ''),
                'ele_estado'  => (string) ($data['ele_estado'] ?? 'H'),
                'ele_icono'   => ($data['ele_icono'] ?? '') !== '' ? (string) $data['ele_icono'] : null,
                'ele_orden'   => ($data['ele_orden'] ?? '') !== '' ? (int) $data['ele_orden'] : null,
                'ele_tipo'    => (string) ($data['ele_tipo'] ?? 'M'),
                'ele_padre'   => ($data['ele_padre'] ?? '') !== '' ? (int) $data['ele_padre'] : null,
                'ele_tarea'   => (string) ($data['ele_tarea'] ?? 'ACCEDER'),
            ]);

            $this->syncTareas($id, $tareaIds);
            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteElemento(string $id): bool
    {
        $this->db->beginTransaction();

        try {
            $this->db->query(
                "DELETE FROM {$this->elementoTareaTable} WHERE eta_ele_id = :id",
                ['id' => $id]
            );

            $result = $this->delete($id);
            $this->db->commit();

            return $result;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function existsByNombre(string $nombre, ?string $excludeId = null): bool
    {
        return $this->existsIn($this->elementoTable, 'ele_nombre', trim($nombre), 'ele_id', $excludeId);
    }

    public function isLinked(string $id): bool
    {
        return $this->linkedTo($this->elementoTareaTable, 'eta_ele_id', $id);
    }

    public function isLinkedToPermiso(string $id): bool
    {
        return $this->linkedTo($this->tableName('permiso'), 'pmo_ele_id', $id);
    }

    private function syncTareas(string $eleId, array $tareaIds): void
    {
        $this->db->query(
            "DELETE FROM {$this->elementoTareaTable} WHERE eta_ele_id = :ele_id",
            ['ele_id' => $eleId]
        );

        foreach ($tareaIds as $tarId) {
            $tarId = (int) $tarId;
            if ($tarId <= 0) {
                continue;
            }
            $this->db->query(
                "INSERT INTO {$this->elementoTareaTable} (eta_ele_id, eta_tar_id) VALUES (:ele_id, :tar_id)",
                ['ele_id' => $eleId, 'tar_id' => $tarId]
            );
        }
    }
}
