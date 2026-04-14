<?php

namespace Core;

use Core\Database;

class Model
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected string $tablePrefix;
    private string $lastSqlLog = '';

    public function __construct() {

        $dsn = $_ENV['DB_DSN'] ?? $_ENV['DB_NAME'] ?? '';
        if ($dsn === '') {
            throw new \RuntimeException('Missing DB_DSN/DB_NAME in environment configuration.');
        }

        if (strpos($dsn, ':') === false) {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $host, $dsn, $charset);
        }

        $this->db = new Database($dsn, (string) ($_ENV['DB_USER'] ?? ''), (string) ($_ENV['DB_PASS'] ?? ''));
        $this->tablePrefix = (string) ($_ENV['DB_PREFIX'] ?? 'wr_');
    }

    public function setTable(string $table): void {

        $this->table = $this->tableName($table);
    }

    public function setPrimaryKey(string $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }

    protected function tableName(string $table): string
    {
        if ($this->tablePrefix === '') {
            return $table;
        }

        if (str_starts_with($table, $this->tablePrefix)) {
            return $table;
        }

        return $this->tablePrefix . $table;
    }

    public function find(int|string $id): array|false {

        $sql = "SELECT * FROM $this->table WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->fetch();
    }

    public function findAll(): array {

        $sql = "SELECT * FROM $this->table";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function create(array $data): string {

        $fields = implode(',', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO $this->table ($fields) VALUES ($values)";
        $this->db->query($sql, $data);
        $id = $this->db->lastInsertId();

        $logValues = implode(',', array_map(fn($v) => $v === null ? '/NULL/' : '/' . $v . '/', array_values($data)));
        $this->lastSqlLog = "INSERT INTO {$this->table}({$fields}) VALUES ({$logValues})";

        return $id;
    }

    public function update(int|string $id, array $data): bool {

        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= "$key = :$key, ";
        }
        $fields = rtrim($fields, ', ');
        $sql = "UPDATE $this->table SET $fields WHERE {$this->primaryKey} = :id";
        $data['id'] = $id;
        $this->db->query($sql, $data);

        $sets = [];
        foreach ($data as $k => $v) {
            if ($k === 'id') continue;
            $sets[] = $k . '=' . ($v === null ? '/NULL/' : '/' . $v . '/');
        }
        $this->lastSqlLog = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE {$this->primaryKey}=/{$id}/";

        return true;
    }

    public function delete(int|string $id): bool {

        $sql = "DELETE FROM $this->table WHERE {$this->primaryKey} = :id";
        $this->db->query($sql, ['id' => $id]);

        $this->lastSqlLog = "DELETE FROM {$this->table} WHERE {$this->primaryKey}=/{$id}/";

        return true;
    }

    public function getLastSqlLog(): string
    {
        return $this->lastSqlLog;
    }

    public function getLastInsertId(): string {

        return $this->db->lastInsertId();
    }
}