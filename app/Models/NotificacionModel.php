<?php

namespace App\Models;

use Core\Model;

class NotificacionModel extends Model
{
    private string $notificacionTable;

    public function __construct()
    {
        parent::__construct();
        $this->notificacionTable = $this->tableName('notificacion');
        $this->setTable('notificacion');
        $this->setPrimaryKey('noti_id');
    }

    public function paginate(int $offset, int $limit, string $search = '', string $modulo = '', string $leida = ''): array
    {
        [$where, $params] = $this->buildWhere($search, $modulo, $leida);

        $sql = "SELECT noti_id, noti_titulo, noti_mensaje, noti_tipo, noti_modulo,
                       noti_accion, noti_usu_origen, noti_fecha, noti_leida,
                       noti_leida_en, noti_referencia_id
                FROM {$this->notificacionTable}
                {$where}
                ORDER BY noti_id DESC
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

    public function countAll(string $search = '', string $modulo = '', string $leida = ''): int
    {
        [$where, $params] = $this->buildWhere($search, $modulo, $leida);

        $sql = "SELECT COUNT(*) AS total FROM {$this->notificacionTable} {$where}";
        $row = $this->db->query($sql, $params)->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function findById(string $id): ?array
    {
        $sql = "SELECT noti_id, noti_titulo, noti_mensaje, noti_tipo, noti_modulo,
                       noti_accion, noti_usu_origen, noti_fecha, noti_leida,
                       noti_leida_en, noti_referencia_id
                FROM {$this->notificacionTable}
                WHERE noti_id = :id
                LIMIT 1";

        $row = $this->db->query($sql, ['id' => $id])->fetch();
        return is_array($row) ? $row : null;
    }

    /** Cantidad de notificaciones no leidas. Usado para el badge del menu. */
    public function countNoLeidas(): int
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->notificacionTable} WHERE noti_leida = 0";
        $row = $this->db->query($sql, [])->fetch();
        return (int) ($row['total'] ?? 0);
    }

    /** Marca una notificacion como leida. Retorna true si se actualizo al menos una fila. */
    public function marcarLeida(string $id): bool
    {
        $sql = "UPDATE {$this->notificacionTable}
                SET noti_leida = 1, noti_leida_en = :ahora
                WHERE noti_id = :id AND noti_leida = 0";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':ahora', date('Y-m-d H:i:s'), \PDO::PARAM_STR);
        $stmt->bindValue(':id',    $id,                  \PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /** Crea una notificacion nueva (llamado desde NotificacionService). */
    public function createRecord(array $data): string
    {
        return $this->create([
            'noti_titulo'        => (string) ($data['noti_titulo']     ?? ''),
            'noti_mensaje'       => (string) ($data['noti_mensaje']    ?? ''),
            'noti_tipo'          => (string) ($data['noti_tipo']       ?? 'info'),
            'noti_modulo'        => (string) ($data['noti_modulo']     ?? ''),
            'noti_accion'        => (string) ($data['noti_accion']     ?? ''),
            'noti_usu_origen'    => (string) ($data['noti_usu_origen'] ?? ''),
            'noti_fecha'         => (string) ($data['noti_fecha']      ?? date('Y-m-d H:i:s')),
            'noti_leida'         => 0,
            'noti_leida_en'      => null,
            'noti_referencia_id' => ($data['noti_referencia_id'] ?? '') !== ''
                                        ? (string) $data['noti_referencia_id']
                                        : null,
        ]);
    }

    /** @return array{string, array<string,string>} */
    private function buildWhere(string $search, string $modulo = '', string $leida = ''): array
    {
        $conditions = [];
        $params     = [];

        if ($search !== '') {
            $conditions[] = '(noti_titulo LIKE :search OR noti_usu_origen LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($modulo !== '') {
            $conditions[] = 'noti_modulo = :modulo';
            $params['modulo'] = $modulo;
        }

        if ($leida === '0' || $leida === '1') {
            $conditions[] = 'noti_leida = :leida';
            $params['leida'] = $leida;
        }

        $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$where, $params];
    }
}
