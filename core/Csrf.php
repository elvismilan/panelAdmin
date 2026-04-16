<?php

namespace Core;

class Csrf
{
    private const SESSION_KEY = '_csrf_token';
    private const FIELD_NAME  = '_csrf_token';

    public static function token(): string
    {
        Session::start();
        $token = Session::get(self::SESSION_KEY);

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set(self::SESSION_KEY, $token);
        }

        return $token;
    }

    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="' . self::FIELD_NAME . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate(array $params): bool
    {
        $submitted = (string) ($params[self::FIELD_NAME] ?? '');
        if ($submitted === '') {
            return false;
        }

        $stored = (string) (Session::get(self::SESSION_KEY) ?? '');
        if ($stored === '') {
            return false;
        }

        return hash_equals($stored, $submitted);
    }
}
