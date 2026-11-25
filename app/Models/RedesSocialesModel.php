<?php

namespace App\Models;

use PanelAdmin\Core\Contracts\SearchablePaginated;
use PanelAdmin\Core\Model;

class RedesSocialesModel extends Model implements SearchablePaginated
{
    private string $tableRedes;

    public function __construct()
    {
        parent::__construct();
        $this->tableRedes = $this->tableName('redes_sociales');
        $this->setTable('redes_sociales');
        $this->setPrimaryKey('res_id');
    }

    public function paginate(int $offset, int $limit, string $search = ''): array
    {
        [$where, $params] = $this->buildWhere($search);

        $sql = "SELECT res_id, res_titulo, res_link, res_icon, res_estado, created, modified
                FROM {$this->tableRedes}
                {$where}
                ORDER BY res_id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(string $search = ''): int
    {
        [$where, $params] = $this->buildWhere($search);

        $sql = "SELECT COUNT(*) AS total FROM {$this->tableRedes} {$where}";
        $row = $this->db->query($sql, $params)->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function findById(string $id): ?array
    {
        $sql = "SELECT res_id, res_titulo, res_link, res_icon, res_estado, created, modified
                FROM {$this->tableRedes}
                WHERE res_id = :id
                LIMIT 1";

        $row = $this->db->query($sql, ['id' => $id])->fetch();

        return is_array($row) ? $row : null;
    }

    public function getActiveForHome(int $limit = 20): array
    {
        $safeLimit = max(1, min($limit, 100));

        $sql = "SELECT res_id, res_titulo, res_link, res_icon
                FROM {$this->tableRedes}
                WHERE res_estado = 'H'
                ORDER BY res_id ASC
                LIMIT :limit";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $safeLimit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function createRecord(array $data): string
    {
        return $this->create([
            'res_titulo' => trim((string) ($data['res_titulo'] ?? '')),
            'res_link' => trim((string) ($data['res_link'] ?? '')),
            'res_icon' => trim((string) ($data['res_icon'] ?? '')),
            'res_estado' => trim((string) ($data['res_estado'] ?? 'H')),
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateRecord(string $id, array $data): bool
    {
        return $this->update($id, [
            'res_titulo' => trim((string) ($data['res_titulo'] ?? '')),
            'res_link' => trim((string) ($data['res_link'] ?? '')),
            'res_icon' => trim((string) ($data['res_icon'] ?? '')),
            'res_estado' => trim((string) ($data['res_estado'] ?? 'H')),
            'modified' => date('Y-m-d H:i:s'),
        ]);
    }

    public function deleteRecord(string $id): bool
    {
        return $this->delete($id);
    }

    public function existsByTitle(string $titulo, ?string $excludeId = null): bool
    {
        return $this->existsIn($this->tableRedes, 'res_titulo', trim($titulo), 'res_id', $excludeId);
    }

    /** @return array{string, array<string,string>} */
    private function buildWhere(string $search): array
    {
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = '(res_titulo LIKE :search OR res_link LIKE :search2 OR res_icon LIKE :search3)';
            $params['search'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
            $params['search3'] = '%' . $search . '%';
        }

        $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$where, $params];
    }
}
