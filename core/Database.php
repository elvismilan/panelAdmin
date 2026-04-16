<?php

namespace Core;

use PDO;
use PDOStatement;

class Database
{
    /** @var array<string, self> */
    private static array $instances = [];

    private PDO $pdo;
    private string $dsn;
    private string $username;
    private string $password;

    public static function getInstance(string $dsn, string $username, string $password): self
    {
        $key = $dsn . '|' . $username;
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($dsn, $username, $password);
        }

        return self::$instances[$key];
    }

    public function __construct(string $dsn, string $username, string $password) {

        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->connect();
    }

    private function connect(): void {

        $this->pdo = new PDO($this->dsn, $this->username, $this->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public function query(string $sql, array $params = []): PDOStatement {

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function lastInsertId(): string {

        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): void {

        $this->pdo->beginTransaction();
    }

    public function commit(): void {

        $this->pdo->commit();
    }

    public function rollBack(): void {

        $this->pdo->rollBack();
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}