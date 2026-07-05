<?php

namespace Core;

use Core\Database;
use PDO;
use PDOStatement;

class Model
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey  = 'id';
    protected bool   $softDeletes = false;
    protected string $deletedAtColumn = 'deleted_at';
    private array $lastActionLog = [];

    public function __construct() {
        $this->db = Database::fromEnv();
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
        return TableNameResolver::resolve($this->db, $table);
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

        $this->lastActionLog = ['op' => 'INSERT', 'table' => $this->table, 'data' => $data, 'id' => $id];

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

        $this->lastActionLog = ['op' => 'UPDATE', 'table' => $this->table, 'pk' => $id, 'data' => array_diff_key($data, ['id' => ''])];

        return true;
    }

    public function delete(int|string $id): bool
    {
        if ($this->softDeletes) {
            $col = $this->deletedAtColumn;
            $sql = "UPDATE {$this->table} SET {$col} = NOW() WHERE {$this->primaryKey} = :id AND {$col} IS NULL";
            $this->db->query($sql, ['id' => $id]);
            $this->lastActionLog = ['op' => 'SOFT_DELETE', 'table' => $this->table, 'pk' => $id];
        } else {
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
            $this->db->query($sql, ['id' => $id]);
            $this->lastActionLog = ['op' => 'DELETE', 'table' => $this->table, 'pk' => $id];
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
        $this->lastActionLog = ['op' => 'RESTORE', 'table' => $this->table, 'pk' => $id];

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

    public function getLastActionLog(): string
    {
        return json_encode($this->lastActionLog, JSON_UNESCAPED_UNICODE) ?: '{}';
    }

    /** @deprecated Usar getLastActionLog() */
    public function getLastSqlLog(): string
    {
        return $this->getLastActionLog();
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

    /**
     * Build a safe pattern for SQL LIKE by escaping wildcard tokens.
     */
    protected function likePattern(string $value): string
    {
        $trimmed = trim($value);
        $escaped = str_replace(
            ['\\', '%', '_'],
            ['\\\\', '\\%', '\\_'],
            $trimmed
        );

        return '%' . $escaped . '%';
    }

    protected function buildWhereClause(array $conditions): string
    {
        return $conditions !== [] ? ' WHERE ' . implode(' AND ', $conditions) : '';
    }

    protected function countByQuery(string $sql, array $params = []): int
    {
        $row = $this->db->query($sql, $params)->fetch();
        return (int) ($row['total'] ?? 0);
    }

    protected function fetchPaginated(string $sql, array $params, int $offset, int $limit): array
    {
        $stmt = $this->db->getConnection()->prepare($sql);
        $this->bindStatementParams($stmt, $params);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    protected function bindStatementParams(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                $value === null => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };

            $stmt->bindValue(':' . $key, $value, $type);
        }
    }

}
