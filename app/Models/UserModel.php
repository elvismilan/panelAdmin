<?php

namespace App\Models;

use Core\Model;

class UserModel extends Model
{
    private string $usuarioTable;
    private string $personaTable;

    public function __construct()
    {
        parent::__construct();
        $this->usuarioTable = $this->tableName('usuario');
        $this->personaTable = $this->tableName('persona');
    }

    public function authenticate(string $username, string $password): ?array
    {
        $sql = "SELECT
                    u.usu_id,
                    u.usu_password,
                    u.usu_per_id,
                    u.usu_gru_id,
                    u.usu_estado,
                    p.per_nombre,
                    p.per_apellido,
                    p.per_foto,
                                        p.per_email
                FROM {$this->usuarioTable} u
                LEFT JOIN {$this->personaTable} p ON p.per_id = u.usu_per_id
                WHERE u.usu_id = :username
                  AND u.usu_estado = :estado
                LIMIT 1";

        $stmt = $this->db->query($sql, [
            'username' => $username,
            'estado' => (string) ($_ENV['AUTH_ACTIVE_STATUS'] ?? 'H'),
        ]);

        $user = $stmt->fetch();
        if (!is_array($user)) {
            return null;
        }

        if (!$this->verifyPassword($password, (string) ($user['usu_password'] ?? ''))) {
            return null;
        }

        $fullName = trim(((string) ($user['per_nombre'] ?? '')) . ' ' . ((string) ($user['per_apellido'] ?? '')));

        return [
            'id' => (string) $user['usu_id'],
            'username' => (string) $user['usu_id'],
            'person_id' => (int) ($user['usu_per_id'] ?? 0),
            'group' => (string) ($user['usu_gru_id'] ?? ''),
            'group_name' => (string) ($user['usu_gru_id'] ?? ''),
            'full_name' => $fullName,
            'email' => (string) ($user['per_email'] ?? ''),
            'photo' => (string) ($user['per_foto'] ?? ''),
            'auth_driver' => 'usuario',
        ];
    }

    private function verifyPassword(string $plainPassword, string $storedPassword): bool
    {
        if ($storedPassword === '') {
            return false;
        }

        $info = password_get_info($storedPassword);
        if (($info['algoName'] ?? 'unknown') !== 'unknown' && password_verify($plainPassword, $storedPassword)) {
            return true;
        }

        $legacySalt = (string) ($_ENV['AUTH_LEGACY_SALT'] ?? ($_ENV['HASH_PASSWORD_KEY'] ?? ''));
        $legacyAlgo = (string) ($_ENV['AUTH_LEGACY_ALGO'] ?? 'sha256');

        if ($legacySalt !== '') {
            $legacyHash = hash_hmac($legacyAlgo, trim($plainPassword), $legacySalt);
            if (hash_equals($storedPassword, $legacyHash)) {
                return true;
            }
        }

        return false;
    }
}
