<?php

namespace App\Models;

use Core\Model;

class NotificacionModel extends Model
{
    private string $notificacionTable;
    private string $notificacionDestinoTable;
    private string $usuarioTable;
    private bool $hasDestinoTable;

    public function __construct()
    {
        parent::__construct();
        $this->notificacionTable = $this->tableName('notificacion');
        $this->notificacionDestinoTable = $this->tableName('notificacion_destino');
        $this->usuarioTable = $this->tableName('usuario');
        $this->hasDestinoTable = $this->tableExistsByName($this->notificacionDestinoTable);

        $this->setTable('notificacion');
        $this->setPrimaryKey('noti_id');
    }

    public function paginate(
        int $offset,
        int $limit,
        string $search = '',
        string $modulo = '',
        string $leida = '',
        ?string $usuDestino = null
    ): array {
        if ($usuDestino !== null) {
            $usuDestino = trim($usuDestino);
            if ($usuDestino === '') {
                return [];
            }

            if ($this->hasDestinoTable) {
                [$where, $params] = $this->buildWhere($search, $modulo, $leida, 'nd.nd_leida');

                $sql = "SELECT n.noti_id, n.noti_titulo, n.noti_mensaje, n.noti_tipo, n.noti_modulo,
                               n.noti_accion, n.noti_usu_origen, n.noti_fecha,
                               nd.nd_leida AS noti_leida,
                               nd.nd_leida_en AS noti_leida_en,
                               n.noti_referencia_id
                        FROM {$this->notificacionTable} n
                        INNER JOIN {$this->notificacionDestinoTable} nd ON nd.nd_noti_id = n.noti_id
                        WHERE nd.nd_usu_id = :usuDestino"
                        . ($where !== '' ? ' AND ' . substr($where, 6) : '')
                        . "\nORDER BY n.noti_id DESC
                        LIMIT :limit OFFSET :offset";

                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->bindValue(':usuDestino', $usuDestino, \PDO::PARAM_STR);
                foreach ($params as $key => $value) {
                    $stmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
                }
                $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
                $stmt->bindValue(':offset', max(0, $offset), \PDO::PARAM_INT);
                $stmt->execute();

                return $stmt->fetchAll();
            }
        }

        [$where, $params] = $this->buildWhere($search, $modulo, $leida, 'noti_leida');

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
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(string $search = '', string $modulo = '', string $leida = '', ?string $usuDestino = null): int
    {
        if ($usuDestino !== null) {
            $usuDestino = trim($usuDestino);
            if ($usuDestino === '') {
                return 0;
            }

            if ($this->hasDestinoTable) {
                [$where, $params] = $this->buildWhere($search, $modulo, $leida, 'nd.nd_leida');

                $sql = "SELECT COUNT(*) AS total
                        FROM {$this->notificacionTable} n
                        INNER JOIN {$this->notificacionDestinoTable} nd ON nd.nd_noti_id = n.noti_id
                        WHERE nd.nd_usu_id = :usuDestino"
                    . ($where !== '' ? ' AND ' . substr($where, 6) : '');

                $row = $this->db->query($sql, ['usuDestino' => $usuDestino] + $params)->fetch();
                return (int) ($row['total'] ?? 0);
            }
        }

        [$where, $params] = $this->buildWhere($search, $modulo, $leida, 'noti_leida');

        $sql = "SELECT COUNT(*) AS total FROM {$this->notificacionTable} {$where}";
        $row = $this->db->query($sql, $params)->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function findById(string $id, ?string $usuDestino = null): ?array
    {
        if ($usuDestino !== null) {
            $usuDestino = trim($usuDestino);
            if ($usuDestino === '') {
                return null;
            }

            if ($this->hasDestinoTable) {
                $sql = "SELECT n.noti_id, n.noti_titulo, n.noti_mensaje, n.noti_tipo, n.noti_modulo,
                               n.noti_accion, n.noti_usu_origen, n.noti_fecha,
                               nd.nd_leida AS noti_leida,
                               nd.nd_leida_en AS noti_leida_en,
                               n.noti_referencia_id
                        FROM {$this->notificacionTable} n
                        INNER JOIN {$this->notificacionDestinoTable} nd ON nd.nd_noti_id = n.noti_id
                        WHERE n.noti_id = :id AND nd.nd_usu_id = :usuDestino
                        LIMIT 1";

                $row = $this->db->query($sql, ['id' => $id, 'usuDestino' => $usuDestino])->fetch();
                return is_array($row) ? $row : null;
            }
        }

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
    public function countNoLeidas(?string $usuDestino = null): int
    {
        if ($usuDestino !== null) {
            $usuDestino = trim($usuDestino);
            if ($usuDestino === '') {
                return 0;
            }

            if ($this->hasDestinoTable) {
                $sql = "SELECT COUNT(*) AS total
                        FROM {$this->notificacionDestinoTable}
                        WHERE nd_usu_id = :usuDestino AND nd_leida = 0";
                $row = $this->db->query($sql, ['usuDestino' => $usuDestino])->fetch();
                return (int) ($row['total'] ?? 0);
            }
        }

        $sql = "SELECT COUNT(*) AS total FROM {$this->notificacionTable} WHERE noti_leida = 0";
        $row = $this->db->query($sql, [])->fetch();
        return (int) ($row['total'] ?? 0);
    }

    /** Marca una notificacion como leida. Retorna true si se actualizo al menos una fila. */
    public function marcarLeida(string $id, ?string $usuDestino = null): bool
    {
        if ($usuDestino !== null) {
            $usuDestino = trim($usuDestino);
            if ($usuDestino === '') {
                return false;
            }

            if ($this->hasDestinoTable) {
                $sql = "UPDATE {$this->notificacionDestinoTable}
                        SET nd_leida = 1,
                            nd_leida_en = :ahora,
                            nd_estado = 'read'
                        WHERE nd_noti_id = :id AND nd_usu_id = :usuDestino AND nd_leida = 0";

                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->bindValue(':ahora', date('Y-m-d H:i:s'), \PDO::PARAM_STR);
                $stmt->bindValue(':id', $id, \PDO::PARAM_STR);
                $stmt->bindValue(':usuDestino', $usuDestino, \PDO::PARAM_STR);
                $stmt->execute();

                return $stmt->rowCount() > 0;
            }
        }

        $sql = "UPDATE {$this->notificacionTable}
                SET noti_leida = 1, noti_leida_en = :ahora
                WHERE noti_id = :id AND noti_leida = 0";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':ahora', date('Y-m-d H:i:s'), \PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, \PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /** Crea una notificacion nueva (llamado desde NotificacionService). */
    public function createRecord(array $data): string
    {
        $notiId = $this->create([
            'noti_titulo'        => (string) ($data['noti_titulo'] ?? ''),
            'noti_mensaje'       => (string) ($data['noti_mensaje'] ?? ''),
            'noti_tipo'          => (string) ($data['noti_tipo'] ?? 'info'),
            'noti_modulo'        => (string) ($data['noti_modulo'] ?? ''),
            'noti_accion'        => (string) ($data['noti_accion'] ?? ''),
            'noti_usu_origen'    => (string) ($data['noti_usu_origen'] ?? ''),
            'noti_fecha'         => (string) ($data['noti_fecha'] ?? date('Y-m-d H:i:s')),
            'noti_leida'         => 0,
            'noti_leida_en'      => null,
            'noti_referencia_id' => ($data['noti_referencia_id'] ?? '') !== ''
                ? (string) $data['noti_referencia_id']
                : null,
        ]);

        if ($this->hasDestinoTable) {
            $destinos = $this->normalizeDestinos($data['noti_destinos'] ?? null);
            if ($destinos === []) {
                $destinos = $this->getActiveUserIds();
            }

            if ($destinos !== []) {
                $sql = "INSERT IGNORE INTO {$this->notificacionDestinoTable}
                        (nd_noti_id, nd_usu_id, nd_estado, nd_leida, nd_leida_en, nd_entregada_en)
                        VALUES (:notiId, :usuId, 'unread', 0, NULL, :entregadaEn)";

                $stmt = $this->db->getConnection()->prepare($sql);
                $entregadaEn = date('Y-m-d H:i:s');
                foreach ($destinos as $usuId) {
                    $stmt->execute([
                        'notiId' => (int) $notiId,
                        'usuId' => $usuId,
                        'entregadaEn' => $entregadaEn,
                    ]);
                }
            }
        }

        return $notiId;
    }

    /** @return array{string, array<string,string>} */
    private function buildWhere(string $search, string $modulo = '', string $leida = '', string $leidaColumn = 'noti_leida'): array
    {
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = '(noti_titulo LIKE :search OR noti_usu_origen LIKE :search)';
            $params['search'] = $this->likePattern($search);
        }

        if ($modulo !== '') {
            $conditions[] = 'noti_modulo = :modulo';
            $params['modulo'] = $modulo;
        }

        if ($leida === '0' || $leida === '1') {
            $conditions[] = "{$leidaColumn} = :leida";
            $params['leida'] = $leida;
        }

        $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$where, $params];
    }

    /** @return string[] */
    private function normalizeDestinos(array|string|null $destinos): array
    {
        if ($destinos === null || $destinos === '') {
            return [];
        }

        $raw = is_array($destinos) ? $destinos : [$destinos];
        $out = [];

        foreach ($raw as $item) {
            $id = trim((string) $item);
            if ($id === '') {
                continue;
            }
            $out[$id] = true;
        }

        return array_keys($out);
    }

    /** @return string[] */
    private function getActiveUserIds(): array
    {
        $rows = $this->db->query(
            "SELECT usu_id FROM {$this->usuarioTable} WHERE usu_estado = 'H'"
        )->fetchAll();

        $ids = [];
        foreach ($rows as $row) {
            $id = trim((string) ($row['usu_id'] ?? ''));
            if ($id !== '') {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    private function tableExistsByName(string $tableName): bool
    {
        $row = $this->db->query(
            'SELECT COUNT(*) AS total
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = :table',
            ['table' => $tableName]
        )->fetch();

        return (int) ($row['total'] ?? 0) > 0;
    }
}
