<?php

namespace App\Models;

use Core\Model;
use Throwable;

class NotificacionModel extends Model
{
    private const DESTINATION_SENTINEL = '__notification_hidden__';

    private string $notificacionTable;
    private string $notificacionDestinoTable;
    private string $lecturaTable;
    private string $usuarioTable;

    public function __construct()
    {
        parent::__construct();
        $this->notificacionTable = $this->tableName('notificacion');
        $this->notificacionDestinoTable = $this->tableName('notificacion_destino');
        $this->lecturaTable = $this->tableName('notificacion_lectura');
        $this->usuarioTable = $this->tableName('usuario');

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
        $usuDestino = trim((string) $usuDestino);
        if ($usuDestino === '') {
            return [];
        }

        [$where, $params] = $this->buildWhereWithLectura($search, $modulo, $leida, $usuDestino);

        $sql = "SELECT n.noti_id, n.noti_titulo, n.noti_mensaje, n.noti_tipo, n.noti_modulo,
                       n.noti_accion, n.noti_usu_origen, n.noti_fecha,
                       IF(r.nrl_noti_id IS NOT NULL, 1, 0) AS noti_leida,
                       r.nrl_leida_en AS noti_leida_en,
                       n.noti_referencia_id
                FROM {$this->notificacionTable} n
                LEFT JOIN {$this->lecturaTable} r
                       ON r.nrl_noti_id = n.noti_id AND r.nrl_usu_id = :lectura_usu_id
                {$where}
                ORDER BY n.noti_id DESC
                LIMIT :limit OFFSET :offset";

        return $this->fetchPaginated($sql, $params, $offset, $limit);
    }

    public function countAll(string $search = '', string $modulo = '', string $leida = '', ?string $usuDestino = null): int
    {
        $usuDestino = trim((string) $usuDestino);
        if ($usuDestino === '') {
            return 0;
        }

        [$where, $params] = $this->buildWhereWithLectura($search, $modulo, $leida, $usuDestino);

        $sql = "SELECT COUNT(*) AS total
                FROM {$this->notificacionTable} n
                LEFT JOIN {$this->lecturaTable} r
                       ON r.nrl_noti_id = n.noti_id AND r.nrl_usu_id = :lectura_usu_id
                {$where}";

        return $this->countByQuery($sql, $params);
    }

    public function findById(string $id, ?string $usuDestino = null): ?array
    {
        $usuDestino = trim((string) $usuDestino);
        if ($usuDestino === '') {
            return null;
        }

        [$visibilitySql, $visibilityParams] = $this->buildVisibilityConstraint($usuDestino, 'n');
        $conditions = ['n.noti_id = :id'];
        $params = [
            'id' => $id,
            'lectura_usu_id' => $usuDestino,
        ];

        if ($visibilitySql !== '') {
            $conditions[] = $visibilitySql;
            $params = array_merge($params, $visibilityParams);
        }

        $sql = "SELECT n.noti_id, n.noti_titulo, n.noti_mensaje, n.noti_tipo, n.noti_modulo,
                       n.noti_accion, n.noti_usu_origen, n.noti_fecha,
                       IF(r.nrl_noti_id IS NOT NULL, 1, 0) AS noti_leida,
                       r.nrl_leida_en AS noti_leida_en,
                       n.noti_referencia_id
                FROM {$this->notificacionTable} n
                LEFT JOIN {$this->lecturaTable} r
                       ON r.nrl_noti_id = n.noti_id AND r.nrl_usu_id = :lectura_usu_id
                WHERE " . implode(' AND ', $conditions) . "
                LIMIT 1";

        $row = $this->db->query($sql, $params)->fetch();
        return is_array($row) ? $row : null;
    }

    public function countNoLeidas(?string $usuDestino = null): int
    {
        $usuDestino = trim((string) $usuDestino);
        if ($usuDestino === '') {
            return 0;
        }

        [$visibilitySql, $visibilityParams] = $this->buildVisibilityConstraint($usuDestino, 'n');
        $conditions = ['r.nrl_noti_id IS NULL'];
        $params = array_merge(['lectura_usu_id' => $usuDestino], $visibilityParams);

        if ($visibilitySql !== '') {
            $conditions[] = $visibilitySql;
        }

        $sql = "SELECT COUNT(*) AS total
                FROM {$this->notificacionTable} n
                LEFT JOIN {$this->lecturaTable} r
                       ON r.nrl_noti_id = n.noti_id AND r.nrl_usu_id = :lectura_usu_id
                WHERE " . implode(' AND ', $conditions);

        $row = $this->db->query($sql, $params)->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function marcarLeida(string $id, ?string $usuDestino = null): bool
    {
        $usuDestino = trim((string) $usuDestino);
        if ($usuDestino === '') {
            return false;
        }

        if (!$this->userCanAccessNotification($id, $usuDestino)) {
            return false;
        }

        $ahora = date('Y-m-d H:i:s');
        $sql = "INSERT IGNORE INTO {$this->lecturaTable} (nrl_noti_id, nrl_usu_id, nrl_leida_en)
                VALUES (:noti_id, :usu_id, :ahora)";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':noti_id', $id, \PDO::PARAM_INT);
        $stmt->bindValue(':usu_id', $usuDestino, \PDO::PARAM_STR);
        $stmt->bindValue(':ahora', $ahora, \PDO::PARAM_STR);
        $stmt->execute();

        $this->db->query(
            "UPDATE {$this->notificacionDestinoTable}
             SET nd_estado = :estado, nd_leida = :leida, nd_leida_en = :leida_en
             WHERE nd_noti_id = :noti_id AND nd_usu_id = :usu_id",
            [
                'estado' => 'read',
                'leida' => 1,
                'leida_en' => $ahora,
                'noti_id' => $id,
                'usu_id' => $usuDestino,
            ]
        );

        return $stmt->rowCount() > 0;
    }

    public function createRecord(array $data): string
    {
        $destinos = $data['noti_destinos'] ?? null;
        $hasExplicitDestinations = $this->hasExplicitDestinations($destinos);

        $this->db->beginTransaction();

        try {
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

            $destinosResueltos = $this->resolveDestinos($destinos);
            if ($destinosResueltos === [] && $hasExplicitDestinations) {
                $destinosResueltos = [self::DESTINATION_SENTINEL];
            }

            if (is_array($destinosResueltos) && $destinosResueltos !== []) {
                $sql = "INSERT IGNORE INTO {$this->notificacionDestinoTable}
                        (nd_noti_id, nd_usu_id, nd_estado, nd_leida, nd_leida_en, nd_entregada_en)
                        VALUES (:notiId, :usuId, 'unread', 0, NULL, :entregadaEn)";

                $stmt = $this->db->getConnection()->prepare($sql);
                $entregadaEn = (string) ($data['noti_fecha'] ?? date('Y-m-d H:i:s'));

                foreach ($destinosResueltos as $usuId) {
                    $stmt->execute([
                        'notiId' => (int) $notiId,
                        'usuId' => $usuId,
                        'entregadaEn' => $entregadaEn,
                    ]);
                }
            }

            $this->db->commit();
            return $notiId;
        } catch (Throwable $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    /** @return array{string, array<string,string>} */
    private function buildWhereWithLectura(string $search, string $modulo, string $leida, string $usuDestino): array
    {
        $conditions = [];
        $params = [
            'lectura_usu_id' => $usuDestino,
        ];

        [$visibilitySql, $visibilityParams] = $this->buildVisibilityConstraint($usuDestino, 'n');
        if ($visibilitySql !== '') {
            $conditions[] = $visibilitySql;
            $params = array_merge($params, $visibilityParams);
        }

        if ($search !== '') {
            $conditions[] = '(n.noti_titulo LIKE :search OR n.noti_usu_origen LIKE :search)';
            $params['search'] = $this->likePattern($search);
        }

        if ($modulo !== '') {
            $conditions[] = 'n.noti_modulo = :modulo';
            $params['modulo'] = $modulo;
        }

        if ($leida === '0' || $leida === '1') {
            $conditions[] = $leida === '1' ? 'r.nrl_noti_id IS NOT NULL' : 'r.nrl_noti_id IS NULL';
        }

        return [
            $this->buildWhereClause($conditions),
            $params,
        ];
    }

    /** @return array{string, array<string,string>} */
    private function buildVisibilityConstraint(string $usuDestino, string $notificationAlias): array
    {
        return [
            '(EXISTS (
                SELECT 1
                FROM ' . $this->notificacionDestinoTable . ' d_vis
                WHERE d_vis.nd_noti_id = ' . $notificationAlias . '.noti_id
                  AND d_vis.nd_usu_id = :destino_usu_id
            ) OR NOT EXISTS (
                SELECT 1
                FROM ' . $this->notificacionDestinoTable . ' d_any
                WHERE d_any.nd_noti_id = ' . $notificationAlias . '.noti_id
            ))',
            ['destino_usu_id' => $usuDestino],
        ];
    }

    private function userCanAccessNotification(string $notificationId, string $usuDestino): bool
    {
        $conditions = ['n.noti_id = :id'];
        $params = ['id' => $notificationId];

        [$visibilitySql, $visibilityParams] = $this->buildVisibilityConstraint($usuDestino, 'n');
        if ($visibilitySql !== '') {
            $conditions[] = $visibilitySql;
            $params = array_merge($params, $visibilityParams);
        }

        $sql = "SELECT 1
                FROM {$this->notificacionTable} n
                WHERE " . implode(' AND ', $conditions) . "
                LIMIT 1";

        return (bool) $this->db->query($sql, $params)->fetchColumn();
    }

    private function hasExplicitDestinations(mixed $destinos): bool
    {
        return !$this->normalizeDestinosPayload($destinos)['global'];
    }

    /** @return array<int,string>|null */
    private function resolveDestinos(mixed $destinos): ?array
    {
        $normalized = $this->normalizeDestinosPayload($destinos);
        if ($normalized['global']) {
            return null;
        }

        if ($normalized['user_ids'] === [] && $normalized['group_ids'] === []) {
            return [];
        }

        $conditions = [];
        $params = [
            'estado' => (string) ($_ENV['AUTH_ACTIVE_STATUS'] ?? 'H'),
        ];

        if ($normalized['user_ids'] !== []) {
            $placeholders = [];
            foreach ($normalized['user_ids'] as $index => $userId) {
                $key = 'user_' . $index;
                $placeholders[] = ':' . $key;
                $params[$key] = $userId;
            }
            $conditions[] = 'usu_id IN (' . implode(', ', $placeholders) . ')';
        }

        if ($normalized['group_ids'] !== []) {
            $placeholders = [];
            foreach ($normalized['group_ids'] as $index => $groupId) {
                $key = 'group_' . $index;
                $placeholders[] = ':' . $key;
                $params[$key] = $groupId;
            }
            $conditions[] = 'usu_gru_id IN (' . implode(', ', $placeholders) . ')';
        }

        if ($conditions === []) {
            return [];
        }

        $sql = "SELECT DISTINCT usu_id
                FROM {$this->usuarioTable}
                WHERE usu_estado = :estado
                  AND (" . implode(' OR ', $conditions) . ")
                ORDER BY usu_id ASC";

        $rows = $this->db->query($sql, $params)->fetchAll();
        $ids = [];
        foreach ($rows as $row) {
            $id = trim((string) ($row['usu_id'] ?? ''));
            if ($id !== '') {
                $ids[$id] = true;
            }
        }

        return array_keys($ids);
    }

    /** @return array{global:bool,user_ids:array<int,string>,group_ids:array<int,string>} */
    private function normalizeDestinosPayload(mixed $destinos): array
    {
        $normalized = [
            'global' => true,
            'user_ids' => [],
            'group_ids' => [],
        ];

        if ($destinos === null || $destinos === '' || $destinos === []) {
            return $normalized;
        }

        if (is_string($destinos) || is_int($destinos)) {
            return [
                'global' => false,
                'user_ids' => [trim((string) $destinos)],
                'group_ids' => [],
            ];
        }

        if (!is_array($destinos)) {
            return $normalized;
        }

        $global = filter_var($destinos['global'] ?? false, FILTER_VALIDATE_BOOL);
        if ($global) {
            return $normalized;
        }

        foreach ($destinos as $key => $value) {
            if (is_int($key) || ctype_digit((string) $key)) {
                $normalized['user_ids'][] = trim((string) $value);
            }
        }

        $this->appendDestinationValues($normalized['user_ids'], $destinos['usuarios'] ?? null);
        $this->appendDestinationValues($normalized['user_ids'], $destinos['users'] ?? null);
        $this->appendDestinationValues($normalized['user_ids'], $destinos['user_ids'] ?? null);
        $this->appendDestinationValues($normalized['group_ids'], $destinos['grupos'] ?? null);
        $this->appendDestinationValues($normalized['group_ids'], $destinos['groups'] ?? null);
        $this->appendDestinationValues($normalized['group_ids'], $destinos['group_ids'] ?? null);

        $normalized['user_ids'] = array_values(array_unique(array_filter(array_map('trim', $normalized['user_ids']), static function (string $value): bool {
            return $value !== '';
        })));
        $normalized['group_ids'] = array_values(array_unique(array_filter(array_map('trim', $normalized['group_ids']), static function (string $value): bool {
            return $value !== '';
        })));
        $normalized['global'] = $normalized['user_ids'] === [] && $normalized['group_ids'] === [];

        return $normalized;
    }

    private function appendDestinationValues(array &$bucket, mixed $values): void
    {
        if ($values === null || $values === '') {
            return;
        }

        if (is_string($values) || is_int($values)) {
            $bucket[] = trim((string) $values);
            return;
        }

        if (!is_array($values)) {
            return;
        }

        foreach ($values as $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $bucket[] = trim((string) $value);
        }
    }
}
