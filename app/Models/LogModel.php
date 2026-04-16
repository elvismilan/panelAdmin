<?php

namespace App\Models;

use Core\Model;

class LogModel extends Model
{
    private string $logsTable;

    public function __construct()
    {
        parent::__construct();
        $this->logsTable = $this->tableName('logs');
        $this->setTable('logs');
        $this->setPrimaryKey('log_id');
    }

    public function paginate(int $offset, int $limit, string $search = '', string $tipo = ''): array
    {
        [$where, $params] = $this->buildWhere($search, $tipo);

        $sql = "SELECT log_id, log_accion, log_usu_id, log_fecha, log_hora,
                       log_tipo_accion, log_ip, log_pc
                FROM {$this->logsTable}
                {$where}
                ORDER BY log_id DESC
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

    public function countAll(string $search = '', string $tipo = ''): int
    {
        [$where, $params] = $this->buildWhere($search, $tipo);

        $sql = "SELECT COUNT(*) AS total FROM {$this->logsTable} {$where}";
        $row = $this->db->query($sql, $params)->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function findById(string $id): ?array
    {
        $sql = "SELECT log_id, log_accion, log_usu_id, log_fecha, log_hora,
                       log_tipo_accion, log_ip, log_pc
                FROM {$this->logsTable}
                WHERE log_id = :id
                LIMIT 1";

        $row = $this->db->query($sql, ['id' => $id])->fetch();
        return is_array($row) ? $row : null;
    }

    /** @return array{string[], string[]} [whereClause, params] */
    private function buildWhere(string $search, string $tipo): array
    {
        $conditions = [];
        $params     = [];

        if ($search !== '') {
            $conditions[] = "(log_accion LIKE :search OR log_usu_id LIKE :search2 OR log_ip LIKE :search3)";
            $params['search']  = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
            $params['search3'] = '%' . $search . '%';
        }

        if ($tipo !== '') {
            $conditions[] = "log_tipo_accion = :tipo";
            $params['tipo'] = $tipo;
        }

        $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$where, $params];
    }
}
