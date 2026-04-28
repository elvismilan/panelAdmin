<?php

namespace Core;

class EmailMessages
{
    public const TEMPLATE_PASSWORD_RESET = 'emails/password-reset';
    public const TEMPLATE_USUARIO_ACCESO = 'emails/usuario-credenciales';

    public static function siteTitle(): string
    {
        return (string) ($_ENV['SITE_TITLE'] ?? 'Web Revolution');
    }

    public static function passwordResetSubject(?string $siteTitle = null): string
    {
        return 'Recuperacion de contrasena - ' . ($siteTitle ?? self::siteTitle());
    }

    public static function setupPasswordSubject(?string $siteTitle = null): string
    {
        return 'Configura tu contrasena - ' . ($siteTitle ?? self::siteTitle());
    }
}
