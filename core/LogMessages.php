<?php

namespace Core;

use Throwable;

class LogMessages
{
    private const CTX_USUARIO_GUARDAR = 'UsuarioController::guardar';
    private const CTX_AUTH_FORGOT = 'AuthController::processForgotPassword';
    private const CTX_AUTH_RESET = 'AuthController::processResetPassword';
    private const CTX_GRUPO_GUARDAR = 'GrupoController::guardar';
    private const CTX_GRUPO_ACTUALIZAR = 'GrupoController::actualizar';
    private const CTX_GRUPO_BORRAR = 'GrupoController::borrar';
    private const CTX_ROUTER_FORBIDDEN = 'Router::isForbidden';
    private const CTX_NOTIFICACION_REGISTRAR = 'NotificacionService::registrar';

    public static function usuarioGuardarMailerError(Throwable $e): string
    {
        return '[' . self::CTX_USUARIO_GUARDAR . '] Error de mail al enviar enlace de configuracion: ' . self::safeThrowable($e);
    }

    public static function usuarioGuardarError(Throwable $e): string
    {
        return '[' . self::CTX_USUARIO_GUARDAR . '] Error al enviar enlace de configuracion: ' . self::safeThrowable($e);
    }

    public static function usuarioGuardarMailerErrorForRecipient(Throwable $e, string $email): string
    {
        return '[' . self::CTX_USUARIO_GUARDAR . '] Error de mail al enviar enlace de configuracion para ' . self::maskIdentifier($email) . ': ' . self::safeThrowable($e);
    }

    public static function usuarioGuardarErrorForRecipient(Throwable $e, string $email): string
    {
        return '[' . self::CTX_USUARIO_GUARDAR . '] Error al enviar enlace de configuracion para ' . self::maskIdentifier($email) . ': ' . self::safeThrowable($e);
    }

    public static function authForgotMailerError(Throwable $e): string
    {
        return '[' . self::CTX_AUTH_FORGOT . '] Mailer error: ' . self::safeThrowable($e);
    }

    public static function authForgotError(Throwable $e): string
    {
        return '[' . self::CTX_AUTH_FORGOT . '] Error: ' . self::safeThrowable($e);
    }

    public static function authForgotMailerErrorForRecipient(Throwable $e, string $email): string
    {
        return '[' . self::CTX_AUTH_FORGOT . '] Mailer error para ' . self::maskIdentifier($email) . ': ' . self::safeThrowable($e);
    }

    public static function authForgotErrorForRecipient(Throwable $e, string $email): string
    {
        return '[' . self::CTX_AUTH_FORGOT . '] Error para ' . self::maskIdentifier($email) . ': ' . self::safeThrowable($e);
    }

    public static function authLoginBlocked(string $ip): string
    {
        return 'Login bloqueado por rate limit: ' . self::maskIp($ip);
    }

    public static function authLoginFailed(string $username): string
    {
        return 'Login fallido: ' . self::maskIdentifier($username);
    }

    public static function authForgotRequested(string $email): string
    {
        return 'Solicitud recuperacion contrasena: ' . self::maskIdentifier($email);
    }

    public static function authResetCompleted(string $email): string
    {
        return 'Contrasena restablecida para: ' . self::maskIdentifier($email);
    }

    public static function authResetError(Throwable $e): string
    {
        return '[' . self::CTX_AUTH_RESET . '] Error: ' . self::safeThrowable($e);
    }

    public static function grupoGuardarError(Throwable $e): string
    {
        return '[' . self::CTX_GRUPO_GUARDAR . '] ' . self::safeThrowable($e);
    }

    public static function grupoActualizarError(Throwable $e): string
    {
        return '[' . self::CTX_GRUPO_ACTUALIZAR . '] ' . self::safeThrowable($e);
    }

    public static function grupoBorrarError(Throwable $e): string
    {
        return '[' . self::CTX_GRUPO_BORRAR . '] ' . self::safeThrowable($e);
    }

    public static function routerPermissionCheckFailed(Throwable $e): string
    {
        return '[' . self::CTX_ROUTER_FORBIDDEN . '] Permission check failed: ' . self::safeThrowable($e);
    }

    public static function notificacionRegistrarError(Throwable $e): string
    {
        return '[' . self::CTX_NOTIFICACION_REGISTRAR . '] ' . self::safeThrowable($e);
    }

    public static function logsFallback(array $payload): string
    {
        $json = json_encode($payload);
        if (!is_string($json)) {
            $json = '{}';
        }

        return '[logs_fallback] ' . $json;
    }

    public static function maskIdentifier(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '***';
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $parts = explode('@', $value, 2);
            $local = $parts[0] ?? '';
            $domain = $parts[1] ?? '';

            $localVisible = substr($local, 0, 2);
            if ($localVisible === '') {
                $localVisible = '*';
            }

            $maskedDomain = self::maskDomain($domain);
            return $localVisible . '***@' . $maskedDomain;
        }

        $visible = substr($value, 0, 2);
        return ($visible === '' ? '*' : $visible) . '***';
    }

    private static function maskDomain(string $domain): string
    {
        if ($domain === '') {
            return '***';
        }

        $segments = explode('.', $domain);
        if (count($segments) < 2) {
            $visible = substr($domain, 0, 1);
            return ($visible === '' ? '*' : $visible) . '***';
        }

        $name = array_shift($segments);
        $tld = implode('.', $segments);
        $nameVisible = substr((string) $name, 0, 1);

        return ($nameVisible === '' ? '*' : $nameVisible) . '***.' . $tld;
    }

    private static function maskIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            if (count($parts) === 4) {
                return $parts[0] . '.' . $parts[1] . '.x.x';
            }
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            return ($parts[0] ?? 'x') . ':' . ($parts[1] ?? 'x') . ':x:x';
        }

        return 'x.x.x.x';
    }

    private static function safeThrowable(Throwable $e): string
    {
        return get_class($e);
    }
}
