<?php

namespace Core;

use Throwable;

final class RbacVersion
{
    private const PARAM_KEY = 'rbac_version';
    private const PARAM_TYPE = 'int';
    private const PARAM_GROUP = 'cache';
    private const PARAM_LABEL = 'Version cache RBAC';
    private const DEFAULT_CHECK_INTERVAL = 30;

    private static bool $requestSyncDone = false;

    private Database $db;
    private string $parametroTable;

    public function __construct()
    {
        $this->db = Database::fromEnv();
        $this->parametroTable = TableNameResolver::resolve($this->db, 'parametro');
    }

    public static function ensureFresh(): void
    {
        if (self::$requestSyncDone) {
            return;
        }

        self::$requestSyncDone = true;

        try {
            (new self())->syncSessionVersion();
        } catch (Throwable) {
        }
    }

    public static function bump(): void
    {
        try {
            $service = new self();
            $version = $service->incrementVersion();

            Session::start();
            RbacCache::clearComputed();
            Session::set(RbacCache::VERSION_KEY, $version);
            Session::set(RbacCache::VERSION_CHECKED_AT_KEY, time());
            self::$requestSyncDone = true;
        } catch (Throwable) {
            RbacCache::clearSession();
            self::$requestSyncDone = false;
        }
    }

    private function syncSessionVersion(): void
    {
        Session::start();

        $now = time();
        $lastCheckedAt = (int) Session::get(RbacCache::VERSION_CHECKED_AT_KEY, 0);
        if ($lastCheckedAt > 0 && ($now - $lastCheckedAt) < $this->checkInterval()) {
            return;
        }

        $currentVersion = $this->readCurrentVersion();
        $sessionVersion = (string) Session::get(RbacCache::VERSION_KEY, '');

        if ($sessionVersion !== '' && $sessionVersion !== $currentVersion) {
            RbacCache::clearComputed();
        }

        Session::set(RbacCache::VERSION_KEY, $currentVersion);
        Session::set(RbacCache::VERSION_CHECKED_AT_KEY, $now);
    }

    private function incrementVersion(): string
    {
        $sql = "INSERT INTO {$this->parametroTable}
                    (par_clave, par_valor, par_tipo, par_grupo, par_label)
                VALUES
                    (:clave, '1', :tipo, :grupo, :label)
                ON DUPLICATE KEY UPDATE
                    par_valor = CAST(COALESCE(NULLIF(par_valor, ''), '0') AS UNSIGNED) + 1,
                    par_tipo = VALUES(par_tipo),
                    par_grupo = VALUES(par_grupo),
                    par_label = VALUES(par_label)";

        $this->db->query($sql, [
            'clave' => self::PARAM_KEY,
            'tipo' => self::PARAM_TYPE,
            'grupo' => self::PARAM_GROUP,
            'label' => self::PARAM_LABEL,
        ]);

        return $this->readCurrentVersion();
    }

    private function readCurrentVersion(): string
    {
        $row = $this->db->query(
            "SELECT par_valor
             FROM {$this->parametroTable}
             WHERE par_clave = :clave
             LIMIT 1",
            ['clave' => self::PARAM_KEY]
        )->fetch();

        $value = trim((string) ($row['par_valor'] ?? '0'));
        return $value !== '' ? $value : '0';
    }

    private function checkInterval(): int
    {
        $raw = (int) ($_ENV['RBAC_VERSION_CHECK_INTERVAL'] ?? self::DEFAULT_CHECK_INTERVAL);
        return max(5, $raw);
    }
}
