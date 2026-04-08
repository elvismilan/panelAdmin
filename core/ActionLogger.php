<?php

namespace Core;

use Throwable;

class ActionLogger
{
    private Database $db;
    private string $logsTable;

    public function __construct()
    {
        $dsn = $_ENV['DB_DSN'] ?? $_ENV['DB_NAME'] ?? '';
        if ($dsn === '') {
            throw new \RuntimeException('Missing DB_DSN/DB_NAME in environment configuration.');
        }

        if (strpos($dsn, ':') === false) {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $host, $dsn, $charset);
        }

        $prefix = (string) ($_ENV['DB_PREFIX'] ?? 'wr_');
        $this->logsTable = $prefix . 'logs';
        $this->db = new Database($dsn, (string) ($_ENV['DB_USER'] ?? ''), (string) ($_ENV['DB_PASS'] ?? ''));
    }

    public function record(array $payload): bool
    {
        $sql = "INSERT INTO {$this->logsTable}
                (log_accion, log_usu_id, log_fecha, log_hora, log_tipo_accion, log_ip, log_pc)
                VALUES
                (:accion, :usuario, :fecha, :hora, :tipo, :ip, :pc)";

        try {
            $this->db->query($sql, [
                'accion' => (string) ($payload['accion'] ?? ''),
                'usuario' => (string) ($payload['usuario'] ?? ''),
                'fecha' => (string) ($payload['fecha'] ?? date('Y-m-d')),
                'hora' => (string) ($payload['hora'] ?? date('H:i:s')),
                'tipo' => (string) ($payload['tipo'] ?? 'GENERAL'),
                'ip' => (string) ($payload['ip'] ?? $this->detectIp()),
                'pc' => (string) ($payload['pc'] ?? $this->detectHost()),
            ]);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function detectIp(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    private function detectHost(): string
    {
        $ip = $this->detectIp();
        $host = @gethostbyaddr($ip);
        return $host ?: 'unknown';
    }
}