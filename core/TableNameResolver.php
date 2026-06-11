<?php

namespace Core;

class TableNameResolver
{
    /**
     * Tablas que usan el prefijo wr_ directo (sin configuración).
     * Solo estas tablas tienen prefijo fijo.
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
        'notificacion_lectura' => true,
        'password_resets' => true,
        'permiso' => true,
        'persona' => true,
        'tarea' => true,
        'usuario' => true,
    ];

    public static function resolve(Database $db, string $baseName): string
    {
        $baseName = trim($baseName);
        if ($baseName === '') {
            return $baseName;
        }

        // Solo las tablas en PREFIXED_TABLES usan wr_
        if (!isset(self::PREFIXED_TABLES[$baseName])) {
            return $baseName;
        }

        // Retorna el nombre con prefijo wr_ directo
        return 'wr_' . $baseName;
    }
}
