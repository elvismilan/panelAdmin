<?php

namespace Core;

class TableNameResolver
{
    private const CORE_PREFIX = 'wr_';

    /**
     * Tablas core que usan el prefijo fijo wr_.
     * Todo modulo de negocio existente o futuro fuera de esta lista
     * debe mantenerse sin prefijo.
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
        'notificacion_lectura' => true,
        'parametro' => true,
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

        // Solo las tablas core incluidas en PREFIXED_TABLES usan wr_.
        if (!isset(self::PREFIXED_TABLES[$baseName])) {
            return $baseName;
        }

        return self::CORE_PREFIX . $baseName;
    }
}
