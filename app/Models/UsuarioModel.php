<?php

namespace App\Models;

use Core\Model;

class UsuarioModel extends Model
{
    private string $usuarioTable;
    private string $personaTable;
    private string $grupoTable;
    private string $logsTable;

    public function __construct()
    {
        parent::__construct();
        $this->usuarioTable = $this->tableName('usuario');
        $this->personaTable = $this->tableName('persona');
        $this->grupoTable   = $this->tableName('grupo');
        $this->logsTable    = $this->tableName('logs');
        $this->setTable('usuario');
        $this->setPrimaryKey('usu_id');
    }

    public function paginate(int $offset, int $limit, string $search = ''): array
    {
        $sql = "SELECT u.usu_id, u.usu_estado, u.usu_gru_id,
                       p.per_nombre, p.per_apellido, p.per_foto,
                       g.gru_descripcion
                FROM {$this->usuarioTable} u
                LEFT JOIN {$this->personaTable} p ON p.per_id = u.usu_per_id
                LEFT JOIN {$this->grupoTable} g ON g.gru_id = u.usu_gru_id";

        if ($search !== '') {
            $sql .= " WHERE u.usu_id LIKE :search1
                         OR p.per_nombre LIKE :search2
                         OR p.per_apellido LIKE :search3
                         OR g.gru_descripcion LIKE :search4";
        }

        $sql .= " ORDER BY u.usu_id ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        if ($search !== '') {
            $stmt->bindValue(':search1', '%' . $search . '%', \PDO::PARAM_STR);
            $stmt->bindValue(':search2', '%' . $search . '%', \PDO::PARAM_STR);
            $stmt->bindValue(':search3', '%' . $search . '%', \PDO::PARAM_STR);
            $stmt->bindValue(':search4', '%' . $search . '%', \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(string $search = ''): int
    {
        $sql    = "SELECT COUNT(*) AS total
                   FROM {$this->usuarioTable} u
                   LEFT JOIN {$this->personaTable} p ON p.per_id = u.usu_per_id
                   LEFT JOIN {$this->grupoTable} g ON g.gru_id = u.usu_gru_id";
        $params = [];

        if ($search !== '') {
            $sql .= " WHERE u.usu_id LIKE :search1
                         OR p.per_nombre LIKE :search2
                         OR p.per_apellido LIKE :search3
                         OR g.gru_descripcion LIKE :search4";
            $params['search1'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
            $params['search3'] = '%' . $search . '%';
            $params['search4'] = '%' . $search . '%';
        }

        $row = $this->db->query($sql, $params)->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function findById(string $id): ?array
    {
        $sql = "SELECT u.usu_id, u.usu_password, u.usu_per_id, u.usu_estado, u.usu_gru_id,
                       p.per_nombre, p.per_apellido
                FROM {$this->usuarioTable} u
                LEFT JOIN {$this->personaTable} p ON p.per_id = u.usu_per_id
                WHERE u.usu_id = :id LIMIT 1";

        $row = $this->db->query($sql, ['id' => $id])->fetch();
        return is_array($row) ? $row : null;
    }

    public function createUsuario(array $data): string
    {
        return $this->create([
            'usu_id'       => trim((string) ($data['usu_id'] ?? '')),
            'usu_password' => password_hash((string) ($data['usu_password'] ?? ''), PASSWORD_DEFAULT),
            'usu_per_id'   => ($data['usu_per_id'] ?? '') !== '' ? (int) $data['usu_per_id'] : null,
            'usu_estado'   => (string) ($data['usu_estado'] ?? 'H'),
            'usu_gru_id'   => (string) ($data['usu_gru_id'] ?? ''),
        ]);
    }

    public function updateUsuario(string $id, array $data): bool
    {
        $fields = [
            'usu_per_id' => ($data['usu_per_id'] ?? '') !== '' ? (int) $data['usu_per_id'] : null,
            'usu_estado' => (string) ($data['usu_estado'] ?? 'H'),
            'usu_gru_id' => (string) ($data['usu_gru_id'] ?? ''),
        ];

        $newPassword = trim((string) ($data['usu_password'] ?? ''));
        if ($newPassword !== '') {
            $fields['usu_password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        return $this->update($id, $fields);
    }

    public function deleteUsuario(string $id): bool
    {
        return $this->delete($id);
    }

    public function hasLogs(string $id): bool
    {
        return $this->linkedTo($this->logsTable, 'log_usu_id', $id);
    }

    public function existsById(string $id, ?string $excludeId = null): bool
    {
        return $this->existsIn($this->usuarioTable, 'usu_id', trim($id), 'usu_id', $excludeId);
    }

    public function getAllGrupos(): array
    {
        $sql = "SELECT gru_id, gru_descripcion
                FROM {$this->grupoTable}
                ORDER BY gru_descripcion ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getPersonasDisponibles(?int $currentPerID = null): array
    {
        if ($currentPerID !== null) {
            $sql = "SELECT p.per_id, p.per_nombre, p.per_apellido
                    FROM {$this->personaTable} p
                    WHERE p.per_id = :currentPerID
                       OR NOT EXISTS (
                           SELECT 1 FROM {$this->usuarioTable} u WHERE u.usu_per_id = p.per_id
                       )
                    ORDER BY p.per_apellido ASC, p.per_nombre ASC";
            return $this->db->query($sql, ['currentPerID' => $currentPerID])->fetchAll();
        }

        $sql = "SELECT p.per_id, p.per_nombre, p.per_apellido
                FROM {$this->personaTable} p
                WHERE NOT EXISTS (
                    SELECT 1 FROM {$this->usuarioTable} u WHERE u.usu_per_id = p.per_id
                )
                ORDER BY p.per_apellido ASC, p.per_nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }
}
