<?php

namespace Core;

class ValidationMessages
{
    public const USUARIO_PASSWORDS_DO_NOT_MATCH = 'Las contrasenas no coinciden.';
    public const USUARIO_ALREADY_EXISTS = 'Ya existe un usuario con ese nombre de usuario.';
    public const USUARIO_PASSWORD_MIN_6 = 'La contrasena debe tener al menos 6 caracteres.';

    public const TAREA_ALREADY_EXISTS = 'Ya existe una tarea con ese nombre.';

    public const PERSONA_EMAIL_INVALID = 'El formato del email no es valido.';
    public const PERSONA_CI_ALREADY_EXISTS = 'Ya existe una persona con ese numero de CI.';

    public const GRUPO_ID_ALREADY_EXISTS = 'Ya existe un grupo con ese ID.';
    public const GRUPO_DESC_ALREADY_EXISTS = 'Ya existe un grupo con esa descripcion.';
    public const GRUPO_DESC_ALREADY_EXISTS_OTHER = 'Ya existe otro grupo con esa descripcion.';
    public const GRUPO_SAVE_ERROR = 'Ocurrio un error al guardar el grupo. Intente nuevamente.';
    public const GRUPO_UPDATE_ERROR = 'Ocurrio un error al actualizar el grupo. Intente nuevamente.';

    public const MODULO_ICON_INVALID = 'El icono seleccionado no es valido.';
    public const MODULO_ALREADY_EXISTS = 'Ya existe un modulo con ese nombre.';

    public const AUTH_REQUIRED_CREDENTIALS = 'Usuario y password son obligatorios.';
    public const AUTH_INVALID_CREDENTIALS = 'Credenciales invalidas.';
    public const FORGOT_EMAIL_INVALID = 'Ingresa un correo electronico valido.';
    public const FORGOT_SUCCESS_NEUTRAL = 'Si ese correo esta registrado, recibiras un enlace en los proximos minutos.';
    public const RESET_PASSWORD_MIN_8 = 'La contrasena debe tener al menos 8 caracteres.';
    public const RESET_PASSWORDS_DO_NOT_MATCH = 'Las contrasenas no coinciden.';
    public const RESET_TOKEN_INVALID = 'Este enlace no es valido o ya expiro. Solicita uno nuevo.';
    public const RESET_TOKEN_INVALID_USED_OR_EXPIRED = 'Este enlace no es valido, ya fue utilizado o expiro. Solicita uno nuevo.';
    public const RESET_TOKEN_CHECK_ERROR = 'Ocurrio un error al verificar el enlace. Intenta de nuevo.';
    public const RESET_SAVE_ERROR = 'Ocurrio un error al guardar la contrasena. Intenta de nuevo.';

    public static function authLoginRateLimit(int $minutes): string
    {
        return 'Demasiados intentos fallidos. Espera ' . $minutes . ' minutos e intenta de nuevo.';
    }

    public static function forgotRateLimit(int $minutes): string
    {
        return 'Demasiadas solicitudes. Espera ' . $minutes . ' minutos e intenta de nuevo.';
    }

    public static function authInvalidCredentialsWithRemaining(?int $remaining): string
    {
        $error = self::AUTH_INVALID_CREDENTIALS;
        if ($remaining !== null && $remaining <= 2 && $remaining > 0) {
            $error .= ' Te queda' . ($remaining === 1 ? '' : 'n') . ' ' . $remaining . ' intento' . ($remaining === 1 ? '' : 's') . '.';
        }

        return $error;
    }
}
