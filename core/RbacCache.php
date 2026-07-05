<?php

namespace Core;

final class RbacCache
{
    public const MENU_KEY = '_menu_cache';
    public const PERMISSION_MATRIX_KEY = '_rbac_permission_matrix';
    public const ELEMENT_MAP_KEY = '_rbac_element_map';
    public const VERSION_KEY = '_rbac_version';
    public const VERSION_CHECKED_AT_KEY = '_rbac_version_checked_at';

    public static function clearComputed(): void
    {
        Session::remove(self::MENU_KEY);
        Session::remove(self::PERMISSION_MATRIX_KEY);
        Session::remove(self::ELEMENT_MAP_KEY);
    }

    public static function clearSession(): void
    {
        self::clearComputed();
        Session::remove(self::VERSION_KEY);
        Session::remove(self::VERSION_CHECKED_AT_KEY);
    }
}
