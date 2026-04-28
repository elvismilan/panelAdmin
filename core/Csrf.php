<?php

namespace Core;

class Csrf
{
    private const SESSION_KEY      = '_csrf_token';
    private const SESSION_TIME_KEY = '_csrf_token_iat';
    private const FIELD_NAME       = '_csrf_token';
    private const DEFAULT_TTL      = 3600;

    public static function token(): string
    {
        Session::start();
        $token = Session::get(self::SESSION_KEY);
        $issuedAt = (int) (Session::get(self::SESSION_TIME_KEY) ?? 0);

        if (!is_string($token) || $token === '' || self::isExpired($issuedAt)) {
            return self::regenerate();
        }

        return $token;
    }

    public static function regenerate(): string
    {
        Session::start();
        $token = bin2hex(random_bytes(32));
        Session::set(self::SESSION_KEY, $token);
        Session::set(self::SESSION_TIME_KEY, time());

        return $token;
    }

    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="' . self::FIELD_NAME . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate(array $params): bool
    {
        Session::start();

        $submitted = (string) ($params[self::FIELD_NAME] ?? '');
        if ($submitted === '') {
            return false;
        }

        $stored = (string) (Session::get(self::SESSION_KEY) ?? '');
        if ($stored === '') {
            return false;
        }

        $issuedAt = (int) (Session::get(self::SESSION_TIME_KEY) ?? 0);
        if (self::isExpired($issuedAt)) {
            self::regenerate();
            return false;
        }

        return hash_equals($stored, $submitted);
    }

    private static function isExpired(int $issuedAt): bool
    {
        if ($issuedAt <= 0) {
            return true;
        }

        $ttl = (int) ($_ENV['CSRF_TOKEN_TTL_SECONDS'] ?? self::DEFAULT_TTL);
        if ($ttl < 60) {
            $ttl = self::DEFAULT_TTL;
        }

        return (time() - $issuedAt) >= $ttl;
    }
}
