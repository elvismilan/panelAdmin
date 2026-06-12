<?php

namespace App\Models;

use Core\Model;

class TareaModel extends Model
{
    private string $tareaTable;
    private string $elementoTareaTable;

    public function __construct()
    {
        parent::__construct();
        $this->tareaTable = $this->tableName('tarea');
        $this->elementoTareaTable = $this->tableName('elemento_tarea');
        $this->setTable('tarea');
        $this->setPrimaryKey('tar_id');
    }

    public function all(): array
    {
        $sql = "SELECT tar_id, tar_nombre FROM {$this->tareaTable} ORDER BY tar_id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function paginate(int $offset, int $limit, string $search = ''): array
    {
        $sql = "SELECT tar_id, tar_nombre FROM {$this->tareaTable}";
        $params = [];

        if ($search !== '') {
            $sql .= " WHERE tar_nombre LIKE :search";
            $params['search'] = $this->likePattern($search);
        }
        $sql .= " ORDER BY tar_id DESC LIMIT :limit OFFSET :offset";

        return $this->fetchPaginated($sql, $params, $offset, $limit);
    }

    public function countAll(string $search = ''): int
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->tareaTable}";
        $params = [];

        if ($search !== '') {
            $sql .= " WHERE tar_nombre LIKE :search";
            $params['search'] = $this->likePattern($search);
        }

        return $this->countByQuery($sql, $params);
    }

    public function findById(string $id): ?array
    {
        $sql = "SELECT tar_id, tar_nombre FROM {$this->tareaTable} WHERE tar_id = :id LIMIT 1";
        $row = $this->db->query($sql, ['id' => $id])->fetch();
        return is_array($row) ? $row : null;
    }

    public function createTask(array $data): string
    {
        return $this->create([
            'tar_nombre' => (string) ($data['tar_nombre'] ?? ''),
        ]);
    }

    public function updateTask(string $id, array $data): bool
    {
        return $this->update($id, [
            'tar_nombre' => (string) ($data['tar_nombre'] ?? ''),
        ]);
    }

    public function deleteTask(string $id): bool
    {
        return $this->delete($id);
    }

    public function existsByName(string $nombre, ?string $excludeId = null): bool
    {
        return $this->existsIn($this->tareaTable, 'tar_nombre', trim($nombre), 'tar_id', $excludeId);
    }

    public function isLinkedToModulo(string $id): bool
    {
        return $this->linkedTo($this->elementoTareaTable, 'eta_tar_id', $id);
    }
}
