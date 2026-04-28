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

    /**
     * Consume un token de reset y actualiza la contrasena de forma atomica.
     * Retorna el email del token consumido o null si el token no era valido.
     */
    public function consumeTokenAndUpdatePassword(string $token, string $newPassword): ?string
    {
        if ($token === '') {
            return null;
        }

        $pdo = $this->db->getConnection();
        $activeStatus = (string) ($_ENV['AUTH_ACTIVE_STATUS'] ?? 'H');
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

        $pdo->beginTransaction();

        try {
            $tokenStmt = $pdo->prepare(
                "SELECT id, email
                 FROM {$this->resetsTable}
                 WHERE token = :token
                   AND used = 0
                   AND created_at >= DATE_SUB(NOW(), INTERVAL " . self::TOKEN_EXPIRY_MINUTES . " MINUTE)
                 LIMIT 1
                 FOR UPDATE"
            );
            $tokenStmt->execute(['token' => $token]);

            $tokenRow = $tokenStmt->fetch();
            if (!is_array($tokenRow)) {
                $pdo->rollBack();
                return null;
            }

            $email = strtolower(trim((string) ($tokenRow['email'] ?? '')));
            if ($email === '') {
                $pdo->rollBack();
                return null;
            }

            $userStmt = $pdo->prepare(
                "SELECT u.usu_id
                 FROM {$this->personaTable} p
                 INNER JOIN {$this->usuarioTable} u ON u.usu_per_id = p.per_id
                 WHERE LOWER(p.per_email) = :email
                   AND u.usu_estado = :estado
                 LIMIT 1
                 FOR UPDATE"
            );
            $userStmt->execute([
                'email' => $email,
                'estado' => $activeStatus,
            ]);

            $userRow = $userStmt->fetch();
            if (!is_array($userRow) || !isset($userRow['usu_id'])) {
                $pdo->rollBack();
                return null;
            }

            $updatePasswordStmt = $pdo->prepare(
                "UPDATE {$this->usuarioTable}
                 SET usu_password = :password
                 WHERE usu_id = :id"
            );
            $updatePasswordStmt->execute([
                'password' => $passwordHash,
                'id' => (string) $userRow['usu_id'],
            ]);

            $consumeStmt = $pdo->prepare(
                "UPDATE {$this->resetsTable}
                 SET used = 1
                 WHERE id = :id
                   AND used = 0"
            );
            $consumeStmt->execute([
                'id' => (int) ($tokenRow['id'] ?? 0),
            ]);

            if ($consumeStmt->rowCount() !== 1) {
                $pdo->rollBack();
                return null;
            }

            $pdo->commit();
            return $email;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }
}
