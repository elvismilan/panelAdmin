<?php

namespace Core;

use App\Models\NotificacionModel;
use Throwable;

/**
 * Servicio estatico para registrar notificaciones del sistema.
 *
 * Uso desde cualquier controller:
 *   NotificacionService::registrar('usuarios', 'CREATE', 'admin', 'juan123');
 */
class NotificacionService
{
    /**
     * Tipos validos y sus etiquetas.
     * Mapeados por accion para elegir automaticamente el tipo visual.
     */
    private const TIPO_POR_ACCION = [
        'CREATE' => 'success',
        'UPDATE' => 'warning',
        'DELETE' => 'danger',
    ];

    /**
     * Etiquetas de modulo legibles.
     */
    private const ETIQUETA_MODULO = [
        'usuarios'    => 'Usuarios',
        'personas'    => 'Personas',
        'grupos'      => 'Grupos',
        'modulos'     => 'Modulos',
        'tareas'      => 'Tareas',
        'logs'        => 'Logs',
    ];

    /**
     * Etiquetas de accion legibles.
     */
    private const ETIQUETA_ACCION = [
        'CREATE' => 'creado',
        'UPDATE' => 'actualizado',
        'DELETE' => 'eliminado',
    ];

    /**
     * Registra una notificacion en la base de datos.
     *
     * @param string      $modulo       Modulo que genera la notificacion (ej: 'usuarios')
     * @param string      $accion       Tipo de accion: CREATE | UPDATE | DELETE
     * @param string      $usuOrigen    Usuario que ejecuto la accion
     * @param string|null $referenciaId ID del registro afectado (opcional)
     * @param string|null $mensajeExtra Texto adicional para el mensaje (opcional)
     */
    public static function registrar(
        string $modulo,
        string $accion,
        string $usuOrigen,
        ?string $referenciaId  = null,
        ?string $mensajeExtra  = null
    ): void {
        try {
            $moduloLabel = self::ETIQUETA_MODULO[$modulo] ?? ucfirst($modulo);
            $accionLabel = self::ETIQUETA_ACCION[$accion] ?? strtolower($accion);
            $tipo        = self::TIPO_POR_ACCION[$accion] ?? 'info';

            $titulo  = $moduloLabel . ' ' . $accionLabel;
            if ($referenciaId !== null && $referenciaId !== '') {
                $titulo .= ': ' . $referenciaId;
            }

            $mensaje = 'El usuario "' . $usuOrigen . '" ha ' . $accionLabel
                . ' un registro en el modulo ' . $moduloLabel . '.';
            if ($mensajeExtra !== null && $mensajeExtra !== '') {
                $mensaje .= ' ' . $mensajeExtra;
            }

            $model = new NotificacionModel();
            $model->createRecord([
                'noti_titulo'        => $titulo,
                'noti_mensaje'       => $mensaje,
                'noti_tipo'          => $tipo,
                'noti_modulo'        => $modulo,
                'noti_accion'        => $accion,
                'noti_usu_origen'    => $usuOrigen,
                'noti_fecha'         => date('Y-m-d H:i:s'),
                'noti_referencia_id' => $referenciaId,
            ]);
        } catch (Throwable $e) {
            error_log('[NotificacionService::registrar] ' . $e->getMessage());
        }
    }
}
