#!/usr/bin/env php
<?php

/**
 * Sistema de migraciones minimo.
 *
 * Uso:
 *   php migrate.php           — ejecuta migraciones pendientes
 *   php migrate.php status    — lista el estado de cada migracion
 *   php migrate.php rollback  — revierte la ultima migracion (requiere archivo *_down.sql)
 */

define('MIGRATIONS_DIR', __DIR__ . '/bd/migrations');
define('ENV_FILE', __DIR__ . '/.env');

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------

if (!file_exists(ENV_FILE)) {
    fwrite(STDERR, "Error: no se encontro el archivo .env\n");
    exit(1);
}

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$command = $argv[1] ?? 'migrate';

// ---------------------------------------------------------------------------
// Conexion PDO
// ---------------------------------------------------------------------------

function buildDsn(): array
{
    $dsn = $_ENV['DB_DSN'] ?? $_ENV['DB_NAME'] ?? '';
    if ($dsn === '') {
        fwrite(STDERR, "Error: DB_DSN o DB_NAME no definidos en .env\n");
        exit(1);
    }

    if (strpos($dsn, ':') === false) {
        $host    = $_ENV['DB_HOST']    ?? 'localhost';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
        $dsn     = sprintf('mysql:host=%s;dbname=%s;charset=%s', $host, $dsn, $charset);
    }

    return [
        'dsn'  => $dsn,
        'user' => $_ENV['DB_USER'] ?? '',
        'pass' => $_ENV['DB_PASS'] ?? '',
    ];
}

function getConnection(): PDO
{
    ['dsn' => $dsn, 'user' => $user, 'pass' => $pass] = buildDsn();
    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function ensureMigrationsTable(PDO $pdo): void
{
    $prefix = $_ENV['DB_PREFIX'] ?? 'wr_';
    $pdo->exec("CREATE TABLE IF NOT EXISTS `{$prefix}migrations` (
        `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `migration`  VARCHAR(255) NOT NULL,
        `run_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_migration` (`migration`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function getApplied(PDO $pdo): array
{
    $prefix = $_ENV['DB_PREFIX'] ?? 'wr_';
    return $pdo->query("SELECT migration FROM `{$prefix}migrations` ORDER BY id ASC")
               ->fetchAll(PDO::FETCH_COLUMN);
}

function markApplied(PDO $pdo, string $migration): void
{
    $prefix = $_ENV['DB_PREFIX'] ?? 'wr_';
    $stmt   = $pdo->prepare("INSERT INTO `{$prefix}migrations` (migration) VALUES (:m)");
    $stmt->execute(['m' => $migration]);
}

function getMigrationFiles(): array
{
    if (!is_dir(MIGRATIONS_DIR)) {
        return [];
    }

    $files = glob(MIGRATIONS_DIR . '/[0-9]*_*.sql');
    if ($files === false) {
        return [];
    }

    // Solo archivos _up o sin sufijo (no _down)
    $files = array_filter($files, function (string $f): bool {
        $base = basename($f, '.sql');
        return !str_ends_with($base, '_down');
    });

    sort($files);
    return array_values($files);
}

// ---------------------------------------------------------------------------
// Comandos
// ---------------------------------------------------------------------------

$pdo = getConnection();
ensureMigrationsTable($pdo);

if ($command === 'status') {
    $applied = array_flip(getApplied($pdo));
    $files   = getMigrationFiles();

    if (empty($files)) {
        echo "No hay archivos de migracion en " . MIGRATIONS_DIR . "\n";
        exit(0);
    }

    echo str_pad('Estado', 10) . "Migracion\n";
    echo str_repeat('-', 60) . "\n";

    foreach ($files as $file) {
        $name   = basename($file, '.sql');
        $status = isset($applied[$name]) ? '[OK]     ' : '[PENDIENTE]';
        echo str_pad($status, 10) . " $name\n";
    }
    exit(0);
}

if ($command === 'migrate') {
    $applied = array_flip(getApplied($pdo));
    $files   = getMigrationFiles();
    $count   = 0;

    foreach ($files as $file) {
        $name = basename($file, '.sql');

        if (isset($applied[$name])) {
            continue;
        }

        $sql = file_get_contents($file);
        if ($sql === false || trim($sql) === '') {
            echo "  [SKIP] $name (archivo vacio o ilegible)\n";
            continue;
        }

        echo "  Ejecutando: $name ... ";
        $pdo->exec($sql);
        markApplied($pdo, $name);
        echo "OK\n";
        $count++;
    }

    echo $count > 0
        ? "\n$count migracion(es) aplicada(s).\n"
        : "\nTodo al dia, no hay migraciones pendientes.\n";

    exit(0);
}

if ($command === 'rollback') {
    $applied = getApplied($pdo);

    if (empty($applied)) {
        echo "No hay migraciones aplicadas.\n";
        exit(0);
    }

    $last     = end($applied);
    $downFile = MIGRATIONS_DIR . '/' . $last . '_down.sql';

    if (!file_exists($downFile)) {
        fwrite(STDERR, "No existe archivo de rollback: {$last}_down.sql\n");
        exit(1);
    }

    $sql = file_get_contents($downFile);
    if ($sql === false || trim($sql) === '') {
        fwrite(STDERR, "Archivo de rollback vacio: {$last}_down.sql\n");
        exit(1);
    }

    echo "  Revirtiendo: $last ... ";
    $pdo->exec($sql);

    $prefix = $_ENV['DB_PREFIX'] ?? 'wr_';
    $stmt   = $pdo->prepare("DELETE FROM `{$prefix}migrations` WHERE migration = :m");
    $stmt->execute(['m' => $last]);

    echo "OK\n";
    exit(0);
}

fwrite(STDERR, "Comando desconocido: $command. Usa: migrate | status | rollback\n");
exit(1);
