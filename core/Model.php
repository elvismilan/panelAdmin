<?php

namespace Core;

use Core\Database;

class Model
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey  = 'id';
    protected string $tablePrefix;
    protected bool   $softDeletes = false;
    protected string $deletedAtColumn = 'deleted_at';
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

        $this->db = Database::getInstance($dsn, (string) ($_ENV['DB_USER'] ?? ''), (string) ($_ENV['DB_PASS'] ?? ''));
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

    public function delete(int|string $id): bool
    {
        if ($this->softDeletes) {
            $col = $this->deletedAtColumn;
            $sql = "UPDATE {$this->table} SET {$col} = NOW() WHERE {$this->primaryKey} = :id AND {$col} IS NULL";
            $this->db->query($sql, ['id' => $id]);
            $this->lastSqlLog = "UPDATE {$this->table} SET {$col}=NOW() WHERE {$this->primaryKey}=/{$id}/";
        } else {
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
            $this->db->query($sql, ['id' => $id]);
            $this->lastSqlLog = "DELETE FROM {$this->table} WHERE {$this->primaryKey}=/{$id}/";
        }

        return true;
    }

    /**
     * Restaura un registro eliminado con soft delete.
     */
    public function restore(int|string $id): bool
    {
        if (!$this->softDeletes) {
            return false;
        }

        $col = $this->deletedAtColumn;
        $this->db->query(
            "UPDATE {$this->table} SET {$col} = NULL WHERE {$this->primaryKey} = :id",
            ['id' => $id]
        );
        $this->lastSqlLog = "UPDATE {$this->table} SET {$col}=NULL WHERE {$this->primaryKey}=/{$id}/";

        return true;
    }

    /**
     * Clausula WHERE para excluir registros eliminados (soft delete).
     * Retorna string vacio si el modelo no usa soft deletes.
     */
    protected function notDeletedClause(string $alias = ''): string
    {
        if (!$this->softDeletes) {
            return '';
        }

        $col = $alias !== '' ? "{$alias}.{$this->deletedAtColumn}" : $this->deletedAtColumn;
        return " AND {$col} IS NULL";
    }

    public function getLastSqlLog(): string
    {
        return $this->lastSqlLog;
    }

    public function getLastInsertId(): string {

        return $this->db->lastInsertId();
    }

    /**
     * Check whether a value already exists in a table column (for uniqueness validation).
     * Optionally exclude one record from the check (e.g. the row being edited).
     *
     * @param string      $table         Fully-qualified table name (with prefix).
     * @param string      $column        Column to match on.
     * @param mixed       $value         Value to look for.
     * @param string|null $excludeColumn Column to use for the exclusion (usually the PK).
     * @param mixed       $excludeValue  Value to exclude (e.g. current record's PK).
     */
    protected function existsIn(
        string $table,
        string $column,
        mixed $value,
        ?string $excludeColumn = null,
        mixed $excludeValue = null
    ): bool {
        $sql    = "SELECT COUNT(*) AS total FROM {$table} WHERE {$column} = :value";
        $params = ['value' => $value];

        if ($excludeColumn !== null && $excludeValue !== null && $excludeValue !== '') {
            $sql .= " AND {$excludeColumn} <> :excludeValue";
            $params['excludeValue'] = $excludeValue;
        }

        $row = $this->db->query($sql, $params)->fetch();
        return (int) ($row['total'] ?? 0) > 0;
    }

    /**
     * Check whether a value is referenced in another table (for cascade/delete guards).
     *
     * @param string $table  Fully-qualified table name (with prefix).
     * @param string $column Column to match on.
     * @param mixed  $value  Value to look for.
     */
    protected function linkedTo(string $table, string $column, mixed $value): bool
    {
        $sql = "SELECT COUNT(*) AS total FROM {$table} WHERE {$column} = :value";
        $row = $this->db->query($sql, ['value' => $value])->fetch();
        return (int) ($row['total'] ?? 0) > 0;
    }
}