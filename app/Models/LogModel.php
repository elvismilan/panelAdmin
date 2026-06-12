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

        return $this->fetchPaginated($sql, $params, $offset, $limit);
    }

    public function countAll(string $search = '', string $tipo = ''): int
    {
        [$where, $params] = $this->buildWhere($search, $tipo);

        $sql = "SELECT COUNT(*) AS total FROM {$this->logsTable}{$where}";
        return $this->countByQuery($sql, $params);
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

    /** @return array{string, array<string, string>} [whereClause, params] */
    private function buildWhere(string $search, string $tipo): array
    {
        $conditions = [];
        $params     = [];

        if ($search !== '') {
            $conditions[] = "(log_accion LIKE :search OR log_usu_id LIKE :search2 OR log_ip LIKE :search3)";
            $pattern = $this->likePattern($search);
            $params['search']  = $pattern;
            $params['search2'] = $pattern;
            $params['search3'] = $pattern;
        }

        if ($tipo !== '') {
            $conditions[] = "log_tipo_accion = :tipo";
            $params['tipo'] = $tipo;
        }

        $where = $this->buildWhereClause($conditions);

        return [$where, $params];
    }
}
