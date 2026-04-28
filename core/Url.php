<?php

namespace Core;

class Url
{
    public static function base(): string
    {
        $configured = self::configuredBase();
        if ($configured !== '') {
            return $configured;
        }

        $scheme = self::resolveScheme();
        $host = self::resolveHost();
        $basePath = self::detectScriptBasePath();

        return $scheme . '://' . $host . $basePath;
    }

    public static function basePath(): string
    {
        $configured = self::configuredBase();
        if ($configured !== '') {
            $path = (string) parse_url($configured, PHP_URL_PATH);
            return self::normalizeBasePath($path);
        }

        return self::detectScriptBasePath();
    }

    public static function to(string $path = '/'): string
    {
        $path = trim($path);
        if ($path === '') {
            return self::base();
        }

        if (self::isAbsoluteReference($path)) {
            return $path;
        }

        $base = self::base();
        if ($path[0] === '/') {
            return $base . $path;
        }

        if ($path[0] === '?') {
            return $base . '/' . $path;
        }

        return $base . '/' . ltrim($path, '/');
    }

    public static function absolutizeHtmlAttributes(string $html): string
    {
        return (string) preg_replace_callback(
            '/\b(href|src|action)\s*=\s*(["\'])([^"\']+)\2/i',
            static function (array $matches): string {
                $attribute = $matches[1];
                $quote = $matches[2];
                $value = $matches[3];

                return $attribute . '=' . $quote . self::to($value) . $quote;
            },
            $html
        );
    }

    private static function configuredBase(): string
    {
        $base = trim((string) ($_ENV['APP_URL'] ?? ''));
        if ($base === '') {
            $base = trim((string) ($_ENV['SITE_ROOT'] ?? ''));
        }

        if ($base === '') {
            return '';
        }

        if (!preg_match('#^https?://#i', $base)) {
            return '';
        }

        return rtrim($base, '/');
    }

    private static function resolveScheme(): string
    {
        $forwardedProto = trim((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        if ($forwardedProto !== '') {
            $parts = explode(',', $forwardedProto);
            $first = strtolower(trim((string) ($parts[0] ?? '')));
            if ($first === 'https' || $first === 'http') {
                return $first;
            }
        }

        $https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));
        if ($https === 'on' || $https === '1') {
            return 'https';
        }

        $port = (string) ($_SERVER['SERVER_PORT'] ?? '');
        if ($port === '443') {
            return 'https';
        }

        return 'http';
    }

    private static function resolveHost(): string
    {
        $forwardedHost = trim((string) ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? ''));
        if ($forwardedHost !== '') {
            $parts = explode(',', $forwardedHost);
            $first = trim((string) ($parts[0] ?? ''));
            if ($first !== '') {
                return $first;
            }
        }

        $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
        if ($host !== '') {
            return $host;
        }

        return trim((string) ($_SERVER['SERVER_NAME'] ?? 'localhost'));
    }

    private static function detectScriptBasePath(): string
    {
        $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        $dir = str_replace('\\', '/', dirname($scriptName));
        return self::normalizeBasePath($dir);
    }

    private static function normalizeBasePath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        if ($path === '' || $path === '.' || $path === '/') {
            return '';
        }

        $path = '/' . trim($path, '/');
        return rtrim($path, '/');
    }

    private static function isAbsoluteReference(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        if ($value[0] === '#') {
            return true;
        }

        return (bool) preg_match('#^(https?:)?//#i', $value)
            || (bool) preg_match('#^(mailto|tel|data|javascript):#i', $value);
    }
}