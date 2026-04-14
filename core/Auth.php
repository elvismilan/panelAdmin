<?php

namespace Core;

class Auth
{
    private const SESSION_KEY = 'auth_user';

    public static function login(array $user): void
    {
        Session::start();
        Session::regenerateId();
        Session::set(self::SESSION_KEY, $user);
    }

    public static function check(): bool
    {
        return Session::has(self::SESSION_KEY);
    }

    public static function user(): ?array
    {
        $user = Session::get(self::SESSION_KEY);
        return is_array($user) ? $user : null;
    }

    public static function logout(): void
    {
        Session::remove(self::SESSION_KEY);
        Session::regenerateId();
    }

    public static function requireAuth(string $redirectTo = '/login'): void
    {
        if (self::check()) {
            return;
        }

        header('Location: ' . $redirectTo);
        exit;
    }
}