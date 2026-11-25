<?php

declare(strict_types=1);

namespace App\Messages;

/**
 * Mensajes de los modulos de negocio de este proyecto.
 * Los del admin base vienen de PanelAdmin\Core\ValidationMessages.
 */
class ValidationMessages extends \PanelAdmin\Core\ValidationMessages
{
    public const PERSONA_EMAIL_INVALID = 'El formato del email no es valido.';
    public const PERSONA_CI_ALREADY_EXISTS = 'Ya existe una persona con ese numero de CI.';
    public const BLOGS_SLUG_ALREADY_EXISTS = 'Ya existe un post con ese slug.';
    public const BLOGS_SLUG_INVALID = 'El slug solo puede contener letras minusculas, numeros y guiones.';
    public const REDES_SOCIALES_ALREADY_EXISTS = 'Ya existe una red social con ese titulo.';
    public const REDES_SOCIALES_ICON_INVALID = 'El icono seleccionado no es valido.';
    public const GALERIA_SLUG_INVALID = 'El slug solo puede contener letras minusculas, numeros y guiones.';
    public const GALERIA_SLUG_ALREADY_EXISTS = 'Ya existe una galeria con ese slug.';
    public const GALERIA_ESTADO_INVALIDO = 'El estado seleccionado no es valido.';
    public const GALERIA_FOTO_ALT_REQUIRED = 'El nombre de la foto es obligatorio (maximo 100 caracteres).';
    public const GALERIA_FOTO_IMAGE_REQUIRED = 'Selecciona una imagen para subir.';
    public const CONTACTO_EMAIL_INVALID = 'Ingresa un correo electronico valido.';
    public const CONTACTO_RATE_LIMIT = 'Has enviado varios mensajes recientemente. Intenta de nuevo mas tarde.';
    public const CONTACTO_SAVE_ERROR = 'No pudimos enviar tu mensaje. Intenta de nuevo.';
    public const CONTACTO_RECAPTCHA_REQUIRED = 'Confirma la verificacion reCAPTCHA para continuar.';
    public const CONTACTO_RECAPTCHA_INVALID = 'No se pudo validar reCAPTCHA. Intentalo nuevamente.';
    public const SERVICIO_SLUG_INVALID = 'El slug solo puede contener letras minusculas, numeros y guiones.';
    public const SERVICIO_SLUG_ALREADY_EXISTS = 'Ya existe un servicio con ese slug.';
    public const SERVICIO_ESTADO_INVALIDO = 'El estado seleccionado no es valido.';
    public const TESTIMONIO_ESTADO_INVALIDO = 'El estado seleccionado no es valido.';
    public const SLIDER_ALREADY_EXISTS = 'Ya existe un slide con ese titulo.';
    public const SLIDER_ESTADO_INVALIDO = 'El estado seleccionado no es valido.';
    public const SLIDER_OVERLAY_INVALIDO = 'El overlay debe ser un valor entre 0 y 1.';
}
