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
        return '[' . self::CTX_USUARIO_GUARDAR . '] Error de mail al enviar enlace de configuracion: ' . $e->getMessage();
    }

    public static function usuarioGuardarError(Throwable $e): string
    {
        return '[' . self::CTX_USUARIO_GUARDAR . '] Error al enviar enlace de configuracion: ' . $e->getMessage();
    }

    public static function authForgotMailerError(Throwable $e): string
    {
        return '[' . self::CTX_AUTH_FORGOT . '] Mailer error: ' . $e->getMessage();
    }

    public static function authForgotError(Throwable $e): string
    {
        return '[' . self::CTX_AUTH_FORGOT . '] Error: ' . $e->getMessage();
    }

    public static function authResetError(Throwable $e): string
    {
        return '[' . self::CTX_AUTH_RESET . '] Error: ' . $e->getMessage();
    }

    public static function grupoGuardarError(Throwable $e): string
    {
        return '[' . self::CTX_GRUPO_GUARDAR . '] ' . $e->getMessage();
    }

    public static function grupoActualizarError(Throwable $e): string
    {
        return '[' . self::CTX_GRUPO_ACTUALIZAR . '] ' . $e->getMessage();
    }

    public static function grupoBorrarError(Throwable $e): string
    {
        return '[' . self::CTX_GRUPO_BORRAR . '] ' . $e->getMessage();
    }

    public static function routerPermissionCheckFailed(Throwable $e): string
    {
        return '[' . self::CTX_ROUTER_FORBIDDEN . '] Permission check failed: ' . $e->getMessage();
    }

    public static function notificacionRegistrarError(Throwable $e): string
    {
        return '[' . self::CTX_NOTIFICACION_REGISTRAR . '] ' . $e->getMessage();
    }

    public static function logsFallback(array $payload): string
    {
        $json = json_encode($payload);
        if (!is_string($json)) {
            $json = '{}';
        }

        return '[wr_logs_fallback] ' . $json;
    }
}
