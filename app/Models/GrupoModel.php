<?php

namespace App\Models;

use Core\Model;
use Throwable;

class GrupoModel extends Model
{
    private string $grupoTable;
    private string $usuarioTable;
    private string $permisoTable;
    private string $elementoTable;
    private string $elementoTareaTable;
    private string $tareaTable;

    public function __construct()
    {
        parent::__construct();
        $this->grupoTable        = $this->tableName('grupo');
        $this->usuarioTable      = $this->tableName('usuario');
        $this->permisoTable      = $this->tableName('permiso');
        $this->elementoTable     = $this->tableName('elemento');
        $this->elementoTareaTable = $this->tableName('elemento_tarea');
        $this->tareaTable        = $this->tableName('tarea');
        $this->setTable('grupo');
        $this->setPrimaryKey('gru_id');
    }

    // -------------------------------------------------------------------------
    // Listado con paginacion y busqueda
    // -------------------------------------------------------------------------

    public function paginate(int $offset, int $limit, string $search = ''): array
    {
        $sql = "SELECT g.gru_id, g.gru_descripcion, g.gru_estado,
                       COUNT(DISTINCT u.usu_id) AS total_usuarios
                FROM {$this->grupoTable} g
                LEFT JOIN {$this->usuarioTable} u ON u.usu_gru_id = g.gru_id";
        $params = [];

        if ($search !== '') {
            $sql .= " WHERE g.gru_id LIKE :search1 OR g.gru_descripcion LIKE :search2";
            $pattern = $this->likePattern($search);
            $params['search1'] = $pattern;
            $params['search2'] = $pattern;
        }

        $sql .= " GROUP BY g.gru_id, g.gru_descripcion, g.gru_estado
                  ORDER BY g.gru_descripcion ASC
                  LIMIT :limit OFFSET :offset";

        return $this->fetchPaginated($sql, $params, $offset, $limit);
    }

    public function countAll(string $search = ''): int
    {
        $sql    = "SELECT COUNT(*) AS total FROM {$this->grupoTable}";
        $params = [];

        if ($search !== '') {
            $sql .= " WHERE gru_id LIKE :search1 OR gru_descripcion LIKE :search2";
            $pattern = $this->likePattern($search);
            $params['search1'] = $pattern;
            $params['search2'] = $pattern;
        }

        return $this->countByQuery($sql, $params);
    }

    // -------------------------------------------------------------------------
    // Busqueda individual
    // -------------------------------------------------------------------------

    public function findById(string $id): ?array
    {
        $sql = "SELECT gru_id, gru_descripcion, gru_estado
                FROM {$this->grupoTable}
                WHERE gru_id = :id
                LIMIT 1";
        $row = $this->db->query($sql, ['id' => $id])->fetch();
        return is_array($row) ? $row : null;
    }

    // -------------------------------------------------------------------------
    // Crear / Actualizar / Eliminar
    // -------------------------------------------------------------------------

    public function createGrupo(array $data): string
    {
        return $this->create([
            'gru_id'          => trim((string) ($data['gru_id'] ?? '')),
            'gru_descripcion' => trim((string) ($data['gru_descripcion'] ?? '')),
            'gru_estado'      => (string) ($data['gru_estado'] ?? 'H'),
        ]);
    }

    /**
     * Crea grupo y sincroniza permisos en una sola transaccion.
     *
     * @param string[] $permisos
     */
    public function createGrupoWithPermisos(array $data, array $permisos): string
    {
        $this->db->beginTransaction();

        try {
            $gruId = $this->createGrupo($data);
            $this->syncPermisosInternal($gruId, $permisos);
            $this->db->commit();

            return $gruId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateGrupo(string $id, array $data): bool
    {
        return $this->update($id, [
            'gru_descripcion' => trim((string) ($data['gru_descripcion'] ?? '')),
            'gru_estado'      => (string) ($data['gru_estado'] ?? 'H'),
        ]);
    }

    /**
     * Actualiza grupo y sincroniza permisos en una sola transaccion.
     *
     * @param string[] $permisos
     */
    public function updateGrupoWithPermisos(string $id, array $data, array $permisos): bool
    {
        $this->db->beginTransaction();

        try {
            $result = $this->updateGrupo($id, $data);
            $this->syncPermisosInternal($id, $permisos);
            $this->db->commit();

            return $result;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteGrupo(string $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Elimina permisos y grupo en una sola transaccion.
     */
    public function deleteGrupoWithPermisos(string $id): bool
    {
        $this->db->beginTransaction();

        try {
            $this->syncPermisosInternal($id, []);
            $result = $this->deleteGrupo($id);
            $this->db->commit();

            return $result;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Verificaciones de integridad
    // -------------------------------------------------------------------------

    public function existsById(string $id, ?string $excludeId = null): bool
    {
        return $this->existsIn($this->grupoTable, 'gru_id', trim($id), 'gru_id', $excludeId);
    }

    public function existsByDescripcion(string $descripcion, ?string $excludeId = null): bool
    {
        return $this->existsIn($this->grupoTable, 'gru_descripcion', trim($descripcion), 'gru_id', $excludeId);
    }

    public function hasUsuarios(string $id): bool
    {
        return $this->linkedTo($this->usuarioTable, 'usu_gru_id', $id);
    }

    // -------------------------------------------------------------------------
    // Permisos
    // -------------------------------------------------------------------------

    /**
     * Retorna los permisos del grupo como array de strings "ele_id:tar_id".
     *
     * @return string[]
     */
    public function getPermisos(string $grupoId): array
    {
        $sql  = "SELECT pmo_ele_id, pmo_tar_id
                 FROM {$this->permisoTable}
                 WHERE pmo_gru_id = :gru_id";
        $rows = $this->db->query($sql, ['gru_id' => $grupoId])->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[] = ((int) $row['pmo_ele_id']) . ':' . ((int) $row['pmo_tar_id']);
        }
        return $result;
    }

    /**
     * Retorna todos los elementos activos con sus tareas disponibles.
     * Cada elemento incluye una clave 'tareas' => [['tar_id', 'tar_nombre'], ...].
     *
     * @return array<int, array>
     */
    public function getAllElementos(): array
    {
        $sql = "SELECT ele_id, ele_nombre, ele_titulo, ele_tipo, ele_padre, ele_orden
                FROM {$this->elementoTable}
                WHERE ele_estado = 'H' OR ele_estado IS NULL
                ORDER BY ele_orden ASC, ele_nombre ASC";
        $elementos = $this->db->query($sql)->fetchAll();

        // Cargar tareas disponibles por elemento
        $sqlTareas = "SELECT et.eta_ele_id, t.tar_id, t.tar_nombre
                      FROM {$this->elementoTareaTable} et
                      INNER JOIN {$this->tareaTable} t ON t.tar_id = et.eta_tar_id
                      ORDER BY et.eta_ele_id ASC, t.tar_nombre ASC";
        $tareaRows = $this->db->query($sqlTareas)->fetchAll();

        $tareasPorElemento = [];
        foreach ($tareaRows as $row) {
            $tareasPorElemento[(int) $row['eta_ele_id']][] = [
                'tar_id'     => (int)    $row['tar_id'],
                'tar_nombre' => (string) $row['tar_nombre'],
            ];
        }

        foreach ($elementos as &$el) {
            $el['tareas'] = $tareasPorElemento[(int) $el['ele_id']] ?? [];
        }
        unset($el);

        return $elementos;
    }

    /**
     * Sincroniza los permisos del grupo.
     * $permisos es un array de strings con formato "ele_id:tar_id".
     *
     * @param string   $grupoId
     * @param string[] $permisos
     */
    public function syncPermisos(string $grupoId, array $permisos): void
    {
        $this->db->beginTransaction();

        try {
            $this->syncPermisosInternal($grupoId, $permisos);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * @param string[] $permisos
     */
    private function syncPermisosInternal(string $grupoId, array $permisos): void
    {
        $this->db->query(
            "DELETE FROM {$this->permisoTable} WHERE pmo_gru_id = :gru_id",
            ['gru_id' => $grupoId]
        );

        foreach ($permisos as $pair) {
            $parts = explode(':', (string) $pair, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $eleId = (int) $parts[0];
            $tarId = (int) $parts[1];
            if ($eleId <= 0 || $tarId <= 0) {
                continue;
            }

            $this->db->query(
                "INSERT INTO {$this->permisoTable} (pmo_ele_id, pmo_tar_id, pmo_gru_id)
                 VALUES (:ele_id, :tar_id, :gru_id)",
                [
                    'ele_id' => $eleId,
                    'tar_id' => $tarId,
                    'gru_id' => $grupoId,
                ]
            );
        }
    }
}
