<?php

namespace Core;

class TableNameResolver
{
    /**
     * Tablas del core/auth/permisos que deben usar prefijo configurable.
     *
     * @var array<string, bool>
     */
    private const PREFIXED_TABLES = [
        'elemento' => true,
        'elemento_tarea' => true,
        'grupo' => true,
        'login_attempts' => true,
        'logs' => true,
        'migrations' => true,
        'notificacion' => true,
        'notificacion_destino' => true,
        'password_resets' => true,
        'permiso' => true,
        'persona' => true,
        'tarea' => true,
        'usuario' => true,
    ];

    /** @var array<string, bool> */
    private static array $existsCache = [];

    public static function resolve(Database $db, string $baseName): string
    {
        $baseName = trim($baseName);
        if ($baseName === '') {
            return $baseName;
        }

        if (!isset(self::PREFIXED_TABLES[$baseName])) {
            return $baseName;
        }

        $prefix = trim((string) ($_ENV['DB_PREFIX'] ?? ''));
        if ($prefix === '') {
            return $baseName;
        }

        $prefixed = $prefix . $baseName;
        if (self::tableExists($db, $prefixed)) {
            return $prefixed;
        }

        return $baseName;
    }

    private static function tableExists(Database $db, string $tableName): bool
    {
        if (isset(self::$existsCache[$tableName])) {
            return self::$existsCache[$tableName];
        }

        $stmt = $db->query(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table',
            ['table' => $tableName]
        );

        $exists = (int) $stmt->fetchColumn() > 0;
        self::$existsCache[$tableName] = $exists;
        return $exists;
    }
}
