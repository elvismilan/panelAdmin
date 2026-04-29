<?php

namespace Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (headers_sent()) {
            return;
        }

        @ini_set('session.use_strict_mode', '1');
        @ini_set('session.use_only_cookies', '1');
        @ini_set('session.cookie_httponly', '1');
        @ini_set('session.cookie_samesite', self::cookieSameSite());
        @ini_set('session.cookie_secure', self::cookieSecure() ? '1' : '0');

        $cookieParams = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => (string) $cookieParams['path'],
            'domain'   => self::cookieDomain((string) $cookieParams['domain']),
            'secure'   => self::cookieSecure(),
            'httponly' => true,
            'samesite' => self::cookieSameSite(),
        ]);

        session_start();
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return array_key_exists($key, $_SESSION);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function regenerateId(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    private static function cookieSecure(): bool
    {
        $value = strtolower(trim((string) ($_ENV['SESSION_COOKIE_SECURE'] ?? 'auto')));
        if ($value === '1' || $value === 'true' || $value === 'yes') {
            return true;
        }
        if ($value === '0' || $value === 'false' || $value === 'no') {
            return false;
        }

        $https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));
        return $https === 'on' || $https === '1';
    }

    private static function cookieSameSite(): string
    {
        $value = strtoupper(trim((string) ($_ENV['SESSION_COOKIE_SAMESITE'] ?? 'LAX')));
        return match ($value) {
            'STRICT' => 'Strict',
            'NONE'   => 'None',
            default  => 'Lax',
        };
    }

    private static function cookieDomain(string $default): string
    {
        return trim((string) ($_ENV['SESSION_COOKIE_DOMAIN'] ?? $default));
    }
}
