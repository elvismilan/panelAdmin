<?php

declare(strict_types=1);

namespace App\Messages;

/**
 * Mensajes de los modulos de negocio de este proyecto.
 * Los del admin base vienen de PanelAdmin\Core\FlashMessages.
 */
class FlashMessages extends \PanelAdmin\Core\FlashMessages
{
    public const PERSONA_CREATED = 'Persona registrada correctamente.';
    public const PERSONA_UPDATED = 'Persona actualizada correctamente.';
    public const PERSONA_DELETE_LINKED_FORBIDDEN = 'No se puede eliminar la persona porque tiene un usuario asociado. Elimine primero el usuario.';
    public const PERSONA_DELETED = 'Persona eliminada correctamente.';
    public const BLOGS_CREATED = 'Post creado correctamente.';
    public const BLOGS_UPDATED = 'Post actualizado correctamente.';
    public const BLOGS_DELETED = 'Post eliminado correctamente.';
    public const CONTENIDOS_CREATED = 'Contenido creado correctamente.';
    public const CONTENIDOS_UPDATED = 'Contenido actualizado correctamente.';
    public const CONTENIDOS_DELETED = 'Contenido eliminado correctamente.';
    public const REDES_SOCIALES_CREATED = 'Red social creada correctamente.';
    public const REDES_SOCIALES_UPDATED = 'Red social actualizada correctamente.';
    public const REDES_SOCIALES_DELETED = 'Red social eliminada correctamente.';
    public const GALERIA_CREATED_ADD_PHOTOS = 'Galeria creada correctamente. Ahora puedes agregar las fotos.';
    public const GALERIA_UPDATED = 'Galeria actualizada correctamente.';
    public const GALERIA_DELETED = 'Galeria eliminada correctamente.';
    public const GALERIA_FOTO_CREATED = 'Foto agregada correctamente.';
    public const GALERIA_FOTO_DELETED = 'Foto eliminada correctamente.';
    public const CONTACTO_PUBLIC_RECEIVED = 'Tu mensaje fue enviado correctamente. Te responderemos pronto.';
    public const SERVICIO_CREATED = 'Servicio creado correctamente.';
    public const SERVICIO_UPDATED = 'Servicio actualizado correctamente.';
    public const SERVICIO_DELETED = 'Servicio eliminado correctamente.';
    public const TESTIMONIO_CREATED = 'Testimonio creado correctamente.';
    public const TESTIMONIO_UPDATED = 'Testimonio actualizado correctamente.';
    public const TESTIMONIO_DELETED = 'Testimonio eliminado correctamente.';
    public const SLIDER_CREATED = 'Slide creado correctamente.';
    public const SLIDER_UPDATED = 'Slide actualizado correctamente.';
    public const SLIDER_DELETED = 'Slide eliminado correctamente.';
}
