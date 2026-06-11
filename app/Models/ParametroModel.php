<?php

namespace App\Models;

use Core\Model;

class ParametroModel extends Model
{
    private string $parametroTable;

    public function __construct()
    {
        parent::__construct();
        $this->parametroTable = $this->tableName('parametro');
        $this->setTable('parametro');
        $this->setPrimaryKey('par_id');
    }

    public function paginate(int $offset, int $limit, string $search = ''): array
    {
        [$where, $params] = $this->buildWhere($search);

        $sql = "SELECT par_id, par_clave, par_valor, par_tipo, par_grupo, par_label
                FROM {$this->parametroTable}
                {$where}
                ORDER BY par_grupo ASC, par_clave ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit',  max(1, $limit),  \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function paginateWithFilter(int $offset, int $limit, string $search = '', string $grupo = ''): array
    {
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = "(par_clave LIKE :search OR par_label LIKE :search2)";
            $pattern = $this->likePattern($search);
            $params['search'] = $pattern;
            $params['search2'] = $pattern;
        }

        if ($grupo !== '') {
            $conditions[] = "par_grupo = :grupo";
            $params['grupo'] = $grupo;
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT par_id, par_clave, par_valor, par_tipo, par_grupo, par_label
                FROM {$this->parametroTable}
                {$where}
                ORDER BY par_grupo ASC, par_clave ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit',  max(1, $limit),  \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(string $search = ''): int
    {
        [$where, $params] = $this->buildWhere($search);

        $sql = "SELECT COUNT(*) AS total FROM {$this->parametroTable} {$where}";
        $row = $this->db->query($sql, $params)->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function countAllWithFilter(string $search = '', string $grupo = ''): int
    {
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = "(par_clave LIKE :search OR par_label LIKE :search2)";
            $pattern = $this->likePattern($search);
            $params['search'] = $pattern;
            $params['search2'] = $pattern;
        }

        if ($grupo !== '') {
            $conditions[] = "par_grupo = :grupo";
            $params['grupo'] = $grupo;
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT COUNT(*) AS total FROM {$this->parametroTable} {$where}";
        $row = $this->db->query($sql, $params)->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function findById(string $id): ?array
    {
        $sql = "SELECT par_id, par_clave, par_valor, par_tipo, par_grupo, par_label, par_created_at, par_updated_at
                FROM {$this->parametroTable}
                WHERE par_id = :id
                LIMIT 1";

        $row = $this->db->query($sql, ['id' => $id])->fetch();
        return is_array($row) ? $row : null;
    }

    public function createRecord(array $data): string
    {
        return $this->create([
            'par_clave'  => (string) ($data['par_clave'] ?? ''),
            'par_valor'  => ($data['par_valor'] ?? '') !== '' ? (string) $data['par_valor'] : null,
            'par_tipo'   => (string) ($data['par_tipo'] ?? 'string'),
            'par_grupo'  => (string) ($data['par_grupo'] ?? ''),
            'par_label'  => (string) ($data['par_label'] ?? ''),
        ]);
    }

    public function updateRecord(string $id, array $data): bool
    {
        return $this->update($id, [
            'par_clave'  => (string) ($data['par_clave'] ?? ''),
            'par_valor'  => ($data['par_valor'] ?? '') !== '' ? (string) $data['par_valor'] : null,
            'par_tipo'   => (string) ($data['par_tipo'] ?? 'string'),
            'par_grupo'  => (string) ($data['par_grupo'] ?? ''),
            'par_label'  => (string) ($data['par_label'] ?? ''),
        ]);
    }

    public function deleteRecord(string $id): bool
    {
        return $this->delete($id);
    }

    public function existsByClave(string $clave, ?string $excludeId = null): bool
    {
        return $this->existsIn(
            $this->parametroTable,
            'par_clave',
            trim($clave),
            'par_id',
            $excludeId
        );
    }

    public function getGrupoFilterOptions(): array
    {
        $sql = "SELECT DISTINCT par_grupo AS value, par_grupo AS label, COUNT(*) AS count
                FROM {$this->parametroTable}
                GROUP BY par_grupo
                ORDER BY par_grupo ASC";

        return $this->db->query($sql)->fetchAll();
    }

    /** @return array{string, array<string,string>} */
    private function buildWhere(string $search): array
    {
        $conditions = [];
        $params     = [];

        if ($search !== '') {
            $conditions[] = '(par_clave LIKE :search OR par_label LIKE :search2)';
            $pattern = $this->likePattern($search);
            $params['search'] = $pattern;
            $params['search2'] = $pattern;
        }

        $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$where, $params];
    }
}