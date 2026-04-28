<?php

namespace Core;

class FlashMessages
{
    public const AUTH_PASSWORD_UPDATED = 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.';

    public const USUARIO_CREATED = 'Usuario registrado correctamente.';
    public const USUARIO_CREATED_NO_PERSONA = 'Usuario creado sin persona vinculada. Configura una persona con email para enviar enlace de restablecimiento.';
    public const USUARIO_CREATED_NO_EMAIL = 'Usuario creado, pero la persona vinculada no tiene email. No se pudo enviar enlace de configuración de contraseña.';
    public const USUARIO_CREATED_EMAIL_SEND_FAILED = 'Usuario creado, pero no fue posible enviar el enlace de configuración de contraseña. Intenta reenviar desde recuperación de contraseña.';
    public const USUARIO_UPDATED = 'Usuario actualizado correctamente.';
    public const USUARIO_DELETE_SELF_FORBIDDEN = 'No puedes eliminar tu propio usuario.';
    public const USUARIO_DELETE_HAS_LOGS_FORBIDDEN = 'No se puede eliminar el usuario porque tiene acciones registradas en el sistema.';
    public const USUARIO_DELETED = 'Usuario eliminado correctamente.';

    public const GRUPO_CREATED = 'Grupo registrado correctamente.';
    public const GRUPO_UPDATED = 'Grupo actualizado correctamente.';
    public const GRUPO_DELETE_HAS_USERS_FORBIDDEN = 'No se puede eliminar el grupo porque tiene usuarios asignados.';
    public const GRUPO_DELETE_ERROR = 'Ocurrio un error al eliminar el grupo. Intente nuevamente.';
    public const GRUPO_DELETED = 'Grupo eliminado correctamente.';

    public const TAREA_CREATED = 'Tarea creada correctamente.';
    public const TAREA_UPDATED = 'Tarea actualizada correctamente.';
    public const TAREA_DELETE_LINKED_FORBIDDEN = 'No se puede eliminar la tarea porque esta asociada a uno o mas modulos. Desvincule la tarea de los modulos antes de continuar.';
    public const TAREA_DELETED = 'Tarea eliminada correctamente.';

    public const MODULO_CREATED = 'Modulo creado correctamente.';
    public const MODULO_UPDATED = 'Modulo actualizado correctamente.';
    public const MODULO_DELETE_LINKED_FORBIDDEN = 'No se puede eliminar el modulo porque tiene permisos asignados. Elimine primero los permisos asociados.';
    public const MODULO_DELETED = 'Modulo eliminado correctamente.';

    public const PERSONA_CREATED = 'Persona registrada correctamente.';
    public const PERSONA_UPDATED = 'Persona actualizada correctamente.';
    public const PERSONA_DELETE_LINKED_FORBIDDEN = 'No se puede eliminar la persona porque tiene un usuario asociado. Elimine primero el usuario.';
    public const PERSONA_DELETED = 'Persona eliminada correctamente.';
}
