<?php

namespace Core\Helpers;

class IconHelper
{
    /**
     * Canonical catalog used by forms to avoid invalid icon values.
     *
     * @return array<int, array{value:string,label:string}>
     */
    public static function options(): array
    {
        return [
            ['value' => 'fa fa-home', 'label' => 'Inicio'],
            ['value' => 'fa fa-dashboard', 'label' => 'Dashboard'],
            ['value' => 'fa fa-th-large', 'label' => 'Modulos'],
            ['value' => 'fa fa-list', 'label' => 'Listado'],
            ['value' => 'fa fa-bars', 'label' => 'Menu'],
            ['value' => 'fa fa-sitemap', 'label' => 'Estructura'],
            ['value' => 'fa fa-navicon', 'label' => 'Navegacion'],
            ['value' => 'fa fa-folder', 'label' => 'Carpeta'],
            ['value' => 'fa fa-folder-open', 'label' => 'Carpeta abierta'],
            ['value' => 'fa fa-archive', 'label' => 'Archivo'],
            ['value' => 'fa fa-briefcase', 'label' => 'Portafolio'],
            ['value' => 'fa fa-suitcase', 'label' => 'Maletin'],
            ['value' => 'fa fa-cube', 'label' => 'Caja'],
            ['value' => 'fa fa-cubes', 'label' => 'Cajas'],
            ['value' => 'fa fa-truck', 'label' => 'Envios'],
            ['value' => 'fa fa-shopping-cart', 'label' => 'Carrito'],
            ['value' => 'fa fa-shopping-basket', 'label' => 'Canasta'],
            ['value' => 'fa fa-tags', 'label' => 'Etiquetas'],
            ['value' => 'fa fa-tag', 'label' => 'Etiqueta'],
            ['value' => 'fa fa-file-text-o', 'label' => 'Documento'],
            ['value' => 'fa fa-file-pdf-o', 'label' => 'PDF'],
            ['value' => 'fa fa-file-excel-o', 'label' => 'Excel'],
            ['value' => 'fa fa-file-word-o', 'label' => 'Word'],
            ['value' => 'fa fa-file-image-o', 'label' => 'Imagen'],
            ['value' => 'fa fa-clipboard', 'label' => 'Portapapeles'],
            ['value' => 'fa fa-book', 'label' => 'Libro'],
            ['value' => 'fa fa-bookmark', 'label' => 'Marcador'],
            ['value' => 'fa fa-database', 'label' => 'Base de datos'],
            ['value' => 'fa fa-server', 'label' => 'Servidor'],
            ['value' => 'fa fa-hdd-o', 'label' => 'Disco'],
            ['value' => 'fa fa-users', 'label' => 'Usuarios'],
            ['value' => 'fa fa-user', 'label' => 'Usuario'],
            ['value' => 'fa fa-user-circle-o', 'label' => 'Perfil'],
            ['value' => 'fa fa-id-card-o', 'label' => 'Persona'],
            ['value' => 'fa fa-address-book-o', 'label' => 'Contactos'],
            ['value' => 'fa fa-handshake-o', 'label' => 'Clientes'],
            ['value' => 'fa fa-key', 'label' => 'Permisos'],
            ['value' => 'fa fa-lock', 'label' => 'Seguridad'],
            ['value' => 'fa fa-shield', 'label' => 'Escudo'],
            ['value' => 'fa fa-cog', 'label' => 'Configuracion'],
            ['value' => 'fa fa-cogs', 'label' => 'Configuraciones'],
            ['value' => 'fa fa-wrench', 'label' => 'Herramientas'],
            ['value' => 'fa fa-sliders', 'label' => 'Ajustes'],
            ['value' => 'fa fa-magic', 'label' => 'Asistente'],
            ['value' => 'fa fa-life-ring', 'label' => 'Soporte'],
            ['value' => 'fa fa-calendar', 'label' => 'Calendario'],
            ['value' => 'fa fa-clock-o', 'label' => 'Reloj'],
            ['value' => 'fa fa-check-square-o', 'label' => 'Tareas'],
            ['value' => 'fa fa-check-circle', 'label' => 'Completado'],
            ['value' => 'fa fa-tasks', 'label' => 'Gestion de tareas'],
            ['value' => 'fa fa-bar-chart', 'label' => 'Reportes'],
            ['value' => 'fa fa-pie-chart', 'label' => 'Graficos'],
            ['value' => 'fa fa-line-chart', 'label' => 'Tendencias'],
            ['value' => 'fa fa-area-chart', 'label' => 'Analitica'],
            ['value' => 'fa fa-calculator', 'label' => 'Calculadora'],
            ['value' => 'fa fa-envelope', 'label' => 'Mensajes'],
            ['value' => 'fa fa-comment', 'label' => 'Comentario'],
            ['value' => 'fa fa-comments', 'label' => 'Comentarios'],
            ['value' => 'fa fa-bell', 'label' => 'Notificaciones'],
            ['value' => 'fa fa-bullhorn', 'label' => 'Anuncios'],
            ['value' => 'fa fa-phone', 'label' => 'Telefono'],
            ['value' => 'fa fa-search', 'label' => 'Busqueda'],
            ['value' => 'fa fa-filter', 'label' => 'Filtro'],
            ['value' => 'fa fa-sort', 'label' => 'Ordenar'],
            ['value' => 'fa fa-refresh', 'label' => 'Actualizar'],
            ['value' => 'fa fa-upload', 'label' => 'Subir'],
            ['value' => 'fa fa-download', 'label' => 'Descargar'],
            ['value' => 'fa fa-print', 'label' => 'Imprimir'],
            ['value' => 'fa fa-pencil', 'label' => 'Editar'],
            ['value' => 'fa fa-plus-circle', 'label' => 'Agregar'],
            ['value' => 'fa fa-minus-circle', 'label' => 'Quitar'],
            ['value' => 'fa fa-trash', 'label' => 'Papelera'],
            ['value' => 'fa fa-ban', 'label' => 'Bloquear'],
            ['value' => 'fa fa-credit-card', 'label' => 'Pagos'],
            ['value' => 'fa fa-money', 'label' => 'Finanzas'],
            ['value' => 'fa fa-usd', 'label' => 'Moneda'],
            ['value' => 'fa fa-circle-o', 'label' => 'Circulo'],
        ];
    }

    /**
     * @return string[]
     */
    public static function allowedValues(): array
    {
        return array_column(self::options(), 'value');
    }

    public static function isAllowed(string $iconClass): bool
    {
        return in_array(trim($iconClass), self::allowedValues(), true);
    }

    public static function normalizeForMenu(?string $icon, string $fallback = 'fa fa-circle-o'): string
    {
        $normalized = trim((string) $icon);
        if ($normalized === '') {
            return $fallback;
        }

        // If DB already stores a Font Awesome class, keep it as-is.
        if (preg_match('/\bfa(?:s|r|l|d|b)?\b/i', $normalized) === 1 && preg_match('/\bfa-[a-z0-9-]+\b/i', $normalized) === 1) {
            return $normalized;
        }

        if (self::isAllowed($normalized)) {
            return $normalized;
        }

        // Backward compatibility for old feather names or DB legacy values.
        $legacyMap = [
            'home' => 'fa fa-home',
            'folder' => 'fa fa-folder',
            'circle' => 'fa fa-circle-o',
            'grid' => 'fa fa-th-large',
            'users' => 'fa fa-users',
            'user' => 'fa fa-user',
            'settings' => 'fa fa-cog',
            'tool' => 'fa fa-wrench',
            'tools' => 'fa fa-wrench',
            'file-text' => 'fa fa-file-text-o',
            'database' => 'fa fa-database',
            'list' => 'fa fa-list',
            'layers' => 'fa fa-clone',
            'layout' => 'fa fa-columns',
            'monitor' => 'fa fa-desktop',
            'calendar' => 'fa fa-calendar',
            'clipboard' => 'fa fa-clipboard',
            'check-square' => 'fa fa-check-square-o',
            'bar-chart' => 'fa fa-bar-chart',
            'pie-chart' => 'fa fa-pie-chart',
            'package' => 'fa fa-cube',
            'box' => 'fa fa-cube',
            'airplay' => 'fa fa-television',
            'mail' => 'fa fa-envelope',
            'message-circle' => 'fa fa-comment-o',
            'search' => 'fa fa-search',
            'lock' => 'fa fa-lock',
            'shield' => 'fa fa-shield',
            'key' => 'fa fa-key',
        ];

        $legacyKey = strtolower($normalized);
        if (isset($legacyMap[$legacyKey])) {
            return $legacyMap[$legacyKey];
        }

        return $fallback;
    }

    /**
     * Ensures selected value is available in the select when loading legacy data.
     *
     * @return array<int, array{value:string,label:string}>
     */
    public static function optionsWithSelected(?string $selected): array
    {
        $options = self::options();
        $selectedValue = trim((string) $selected);
        if ($selectedValue === '') {
            return $options;
        }

        foreach ($options as $option) {
            if ($option['value'] === $selectedValue) {
                return $options;
            }
        }

        $options[] = [
            'value' => $selectedValue,
            'label' => 'Actual (legacy): ' . $selectedValue,
        ];

        return $options;
    }
}
