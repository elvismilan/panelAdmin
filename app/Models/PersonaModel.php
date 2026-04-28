<?php

namespace App\Models;

use Core\Model;

class PersonaModel extends Model
{
    private string $personaTable;
    private string $usuarioTable;

    public function __construct()
    {
        parent::__construct();
        $this->personaTable = $this->tableName('persona');
        $this->usuarioTable = $this->tableName('usuario');
        $this->setTable('persona');
        $this->setPrimaryKey('per_id');
    }

    public function paginate(int $offset, int $limit, string $search = ''): array
    {
        $sql = "SELECT per_id, per_nombre, per_apellido, per_email, per_foto, per_ci,
                       per_telefono, per_sexo, per_estado
                FROM {$this->personaTable}";

        if ($search !== '') {
            $sql .= " WHERE per_nombre LIKE :search1
                         OR per_apellido LIKE :search2
                         OR per_ci LIKE :search3
                         OR per_email LIKE :search4";
        }

        $sql .= " ORDER BY per_apellido ASC, per_nombre ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        if ($search !== '') {
            $pattern = $this->likePattern($search);
            $stmt->bindValue(':search1', $pattern, \PDO::PARAM_STR);
            $stmt->bindValue(':search2', $pattern, \PDO::PARAM_STR);
            $stmt->bindValue(':search3', $pattern, \PDO::PARAM_STR);
            $stmt->bindValue(':search4', $pattern, \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(string $search = ''): int
    {
        $sql    = "SELECT COUNT(*) AS total FROM {$this->personaTable}";
        $params = [];

        if ($search !== '') {
            $sql .= " WHERE per_nombre LIKE :search1
                         OR per_apellido LIKE :search2
                         OR per_ci LIKE :search3
                         OR per_email LIKE :search4";
            $pattern = $this->likePattern($search);
            $params['search1'] = $pattern;
            $params['search2'] = $pattern;
            $params['search3'] = $pattern;
            $params['search4'] = $pattern;
        }

        $row = $this->db->query($sql, $params)->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function findById(string $id): ?array
    {
        $sql = "SELECT per_id, per_nombre, per_apellido, per_email, per_foto,
                       per_telefono, per_direccion, per_ci, per_fecha_nacimiento,
                       per_sexo, per_estado
                FROM {$this->personaTable}
                WHERE per_id = :id LIMIT 1";

        $row = $this->db->query($sql, ['id' => $id])->fetch();
        return is_array($row) ? $row : null;
    }

    public function createPersona(array $data): string
    {
        return $this->create([
            'per_nombre'           => (string) ($data['per_nombre'] ?? ''),
            'per_apellido'         => (string) ($data['per_apellido'] ?? ''),
            'per_email'            => ($data['per_email'] ?? '') !== '' ? (string) $data['per_email'] : null,
            'per_foto'             => ($data['per_foto'] ?? '') !== '' ? (string) $data['per_foto'] : null,
            'per_telefono'         => ($data['per_telefono'] ?? '') !== '' ? (string) $data['per_telefono'] : null,
            'per_direccion'        => ($data['per_direccion'] ?? '') !== '' ? (string) $data['per_direccion'] : null,
            'per_ci'               => ($data['per_ci'] ?? '') !== '' ? (string) $data['per_ci'] : null,
            'per_fecha_nacimiento' => ($data['per_fecha_nacimiento'] ?? '') !== '' ? (string) $data['per_fecha_nacimiento'] : null,
            'per_sexo'             => (string) ($data['per_sexo'] ?? 'M'),
            'per_estado'           => (string) ($data['per_estado'] ?? 'A'),
        ]);
    }

    public function updatePersona(string $id, array $data): bool
    {
        return $this->update($id, [
            'per_nombre'           => (string) ($data['per_nombre'] ?? ''),
            'per_apellido'         => (string) ($data['per_apellido'] ?? ''),
            'per_email'            => ($data['per_email'] ?? '') !== '' ? (string) $data['per_email'] : null,
            'per_foto'             => ($data['per_foto'] ?? '') !== '' ? (string) $data['per_foto'] : null,
            'per_telefono'         => ($data['per_telefono'] ?? '') !== '' ? (string) $data['per_telefono'] : null,
            'per_direccion'        => ($data['per_direccion'] ?? '') !== '' ? (string) $data['per_direccion'] : null,
            'per_ci'               => ($data['per_ci'] ?? '') !== '' ? (string) $data['per_ci'] : null,
            'per_fecha_nacimiento' => ($data['per_fecha_nacimiento'] ?? '') !== '' ? (string) $data['per_fecha_nacimiento'] : null,
            'per_sexo'             => (string) ($data['per_sexo'] ?? 'M'),
            'per_estado'           => (string) ($data['per_estado'] ?? 'A'),
        ]);
    }

    public function deletePersona(string $id): bool
    {
        return $this->delete($id);
    }

    public function existsByCi(string $ci, ?string $excludeId = null): bool
    {
        return $this->existsIn($this->personaTable, 'per_ci', trim($ci), 'per_id', $excludeId);
    }

    public function isLinkedToUsuario(string $id): bool
    {
        return $this->linkedTo($this->usuarioTable, 'usu_per_id', $id);
    }
}
