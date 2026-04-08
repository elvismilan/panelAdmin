<?php

namespace Core;

class Permission
{
    private Database $db;
    private string $tablePermiso;
    private string $tableElemento;
    private string $tableGrupo;

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
        $this->tablePermiso = $prefix . 'permiso';
        $this->tableElemento = $prefix . 'elemento';
        $this->tableGrupo = $prefix . 'grupo';

        $this->db = new Database($dsn, (string) ($_ENV['DB_USER'] ?? ''), (string) ($_ENV['DB_PASS'] ?? ''));
    }

    public function canAccessElement(string $groupId, int $elementId): bool
    {
        $sql = "SELECT 1
                FROM {$this->tablePermiso} p
                INNER JOIN {$this->tableElemento} e ON e.ele_id = p.pmo_ele_id
                INNER JOIN {$this->tableGrupo} g ON g.gru_id = p.pmo_gru_id
                WHERE p.pmo_gru_id = :grupo
                  AND p.pmo_ele_id = :elemento
                  AND (e.ele_estado IS NULL OR e.ele_estado = 'H')
                  AND (g.gru_estado IS NULL OR g.gru_estado = 'H')
                LIMIT 1";

        $stmt = $this->db->query($sql, [
            'grupo' => $groupId,
            'elemento' => $elementId,
        ]);

        return (bool) $stmt->fetchColumn();
    }
}