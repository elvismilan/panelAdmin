<?php

namespace Core;

class Auth
{
    private const SESSION_KEY = 'auth_user';

    public static function attempt(string $username, string $password): bool
    {
        $validUser = (string) ($_ENV['AUTH_USERNAME'] ?? 'admin');
        $validHash = (string) ($_ENV['AUTH_PASSWORD_HASH'] ?? '');
        $validPlain = (string) ($_ENV['AUTH_PASSWORD'] ?? 'admin123');

        if (!hash_equals($validUser, $username)) {
            return false;
        }

        if ($validHash !== '') {
            return password_verify($password, $validHash);
        }

        return hash_equals($validPlain, $password);
    }

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