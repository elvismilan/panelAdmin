<?php

namespace Core;

class RateLimiter
{
    private Database $db;
    private string $table;
    private int $maxAttempts;
    private int $windowMinutes;

    public function __construct()
    {
        $this->table        = 'login_attempts';
        $this->maxAttempts  = max(1, (int) ($_ENV['LOGIN_MAX_ATTEMPTS']  ?? 5));
        $this->windowMinutes = max(1, (int) ($_ENV['LOGIN_LOCKOUT_MINUTES'] ?? 15));

        $this->db = Database::fromEnv();
    }

    /**
     * Verifica si la IP superó el límite de intentos en la ventana de tiempo.
     */
    public function tooManyAttempts(string $ip): bool
    {
        return $this->recentAttempts($ip) >= $this->maxAttempts;
    }

    /**
     * Registra un intento fallido para la IP y limpia registros expirados.
     */
    public function hit(string $ip): void
    {
        $this->db->query(
            "INSERT INTO {$this->table} (ip, attempted_at) VALUES (:ip, NOW())",
            ['ip' => $ip]
        );

        $this->clearExpired($ip);
    }

    /**
     * Borra todos los intentos de la IP (llamar tras login exitoso).
     */
    public function clear(string $ip): void
    {
        $this->db->query(
            "DELETE FROM {$this->table} WHERE ip = :ip",
            ['ip' => $ip]
        );
    }

    /**
     * Intentos restantes antes del bloqueo (0 = ya bloqueado).
     */
    public function remainingAttempts(string $ip): int
    {
        return max(0, $this->maxAttempts - $this->recentAttempts($ip));
    }

    /**
     * Minutos configurados para el bloqueo.
     */
    public function lockoutMinutes(): int
    {
        return $this->windowMinutes;
    }

    private function recentAttempts(string $ip): int
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total
             FROM {$this->table}
             WHERE ip = :ip
               AND attempted_at >= DATE_SUB(NOW(), INTERVAL :minutes MINUTE)",
            ['ip' => $ip, 'minutes' => $this->windowMinutes]
        )->fetch();

        return (int) ($row['total'] ?? 0);
    }

    private function clearExpired(string $ip): void
    {
        $this->db->query(
            "DELETE FROM {$this->table}
             WHERE ip = :ip
               AND attempted_at < DATE_SUB(NOW(), INTERVAL :minutes MINUTE)",
            ['ip' => $ip, 'minutes' => $this->windowMinutes]
        );
    }
}
