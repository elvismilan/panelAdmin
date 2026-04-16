<?php

namespace App\Models;

use Core\Model;

class PasswordResetModel extends Model
{
    private string $resetsTable;
    private string $personaTable;
    private string $usuarioTable;

    private const TOKEN_EXPIRY_MINUTES = 60;

    public function __construct()
    {
        parent::__construct();
        $this->resetsTable  = $this->tableName('password_resets');
        $this->personaTable = $this->tableName('persona');
        $this->usuarioTable = $this->tableName('usuario');
    }

    /**
     * Busca un usuario activo por email.
     * Retorna ['usu_id', 'per_email', 'per_nombre'] o null si no existe.
     */
    public function findUserByEmail(string $email): ?array
    {
        $email = trim(strtolower($email));
        if ($email === '') {
            return null;
        }

        $activeStatus = (string) ($_ENV['AUTH_ACTIVE_STATUS'] ?? 'H');

        $sql = "SELECT u.usu_id, p.per_email, p.per_nombre
                FROM {$this->personaTable} p
                INNER JOIN {$this->usuarioTable} u ON u.usu_per_id = p.per_id
                WHERE LOWER(p.per_email) = :email
                  AND u.usu_estado = :estado
                LIMIT 1";

        $row = $this->db->query($sql, [
            'email'  => $email,
            'estado' => $activeStatus,
        ])->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * Invalida tokens anteriores del email y crea uno nuevo.
     * Retorna el token generado (hex de 64 chars).
     */
    public function createToken(string $email): string
    {
        // Invalidar tokens previos no usados
        $this->db->query(
            "UPDATE {$this->resetsTable} SET used = 1 WHERE email = :email AND used = 0",
            ['email' => $email]
        );

        $token = bin2hex(random_bytes(32));

        $this->db->query(
            "INSERT INTO {$this->resetsTable} (email, token, created_at, used)
             VALUES (:email, :token, NOW(), 0)",
            ['email' => $email, 'token' => $token]
        );

        return $token;
    }

    /**
     * Busca un token valido: no usado y no expirado.
     * Retorna la fila o null.
     */
    public function findValidToken(string $token): ?array
    {
        if ($token === '') {
            return null;
        }

        $sql = "SELECT id, email, created_at
                FROM {$this->resetsTable}
                WHERE token      = :token
                  AND used       = 0
                  AND created_at >= DATE_SUB(NOW(), INTERVAL " . self::TOKEN_EXPIRY_MINUTES . " MINUTE)
                LIMIT 1";

        $row = $this->db->query($sql, [
            'token' => $token,
        ])->fetch();

        return is_array($row) ? $row : null;
    }

    /**
     * Marca un token como usado.
     */
    public function markTokenUsed(int $id): void
    {
        $this->db->query(
            "UPDATE {$this->resetsTable} SET used = 1 WHERE id = :id",
            ['id' => $id]
        );
    }

    /**
     * Actualiza la contrasena del usuario con bcrypt.
     */
    public function updatePassword(string $userId, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);

        $this->db->query(
            "UPDATE {$this->usuarioTable} SET usu_password = :password WHERE usu_id = :id",
            ['password' => $hash, 'id' => $userId]
        );

        return true;
    }
}
