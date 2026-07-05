<?php

namespace App\Controllers;

use App\Models\NotificacionModel;
use Core\Auth;
use Core\Controller;
use Throwable;

class NotificacionController extends Controller
{
    private const MODULO_OPTIONS = [
        ['value' => 'usuarios', 'label' => 'Usuarios'],
        ['value' => 'personas', 'label' => 'Personas'],
        ['value' => 'grupos', 'label' => 'Grupos'],
        ['value' => 'modulos', 'label' => 'Modulos'],
        ['value' => 'tareas', 'label' => 'Tareas'],
        ['value' => 'parametros', 'label' => 'Parametros'],
    ];

    private const LEIDA_OPTIONS = [
        ['value' => '0', 'label' => 'No leidas'],
        ['value' => '1', 'label' => 'Leidas'],
    ];

    private const TIPO_OPTIONS = [
        ['value' => 'success', 'label' => 'Creado'],
        ['value' => 'warning', 'label' => 'Actualizado'],
        ['value' => 'danger', 'label' => 'Eliminado'],
        ['value' => 'info', 'label' => 'Info'],
    ];

    // -------------------------------------------------------------------------
    // INDEX
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $this->requireAuth();

        $items   = [];
        $page    = $this->getCurrentPage();
        $perPage = $this->getDefaultPerPage();
        $usuId   = trim((string) (Auth::user()['id'] ?? ''));
        $filters = $this->getQueryParams(['q' => '', 'modulo' => '', 'tipo' => '', 'leida' => '']);
        $search  = $filters['q'];
        $modulo  = $filters['modulo'];
        $tipo    = $filters['tipo'];
        $leida   = $filters['leida'];
        $queryParams = ['q' => $search];
        if ($modulo !== '') {
            $queryParams['modulo'] = $modulo;
        }
        if ($tipo !== '') {
            $queryParams['tipo'] = $tipo;
        }
        if ($leida !== '') {
            $queryParams['leida'] = $leida;
        }

        $pagination = $this->buildPagination(0, $page, $perPage, '/notificaciones', $queryParams);

        try {
            $model = new NotificacionModel();

            $totalRows  = $model->countAll($search, $modulo, $tipo, $leida, $usuId);
            $pagination = $this->buildPagination($totalRows, $page, $perPage, '/notificaciones', $queryParams);
            $items = $model->paginate(
                (int) $pagination['offset'],
                (int) $pagination['perPage'],
                $search,
                $modulo,
                $tipo,
                $leida,
                $usuId
            );
        } catch (Throwable) {
            $items = [];
        }

        $this->renderAdminModule('notificacion/index', [
            'title'      => 'Notificaciones',
            'user'       => Auth::user(),
            'items'      => $items,
            'pagination' => $pagination,
            'search'     => $search,
            'modulo'     => $modulo,
            'tipo'       => $tipo,
            'leida'      => $leida,
            'listToolbarConfig' => [
                'mode' => 'compact',
                'action' => '/notificaciones',
                'method' => 'GET',
                'basePath' => '/notificaciones',
                'queryParams' => $filters,
                'clearUrl' => ($search !== '' || $modulo !== '' || $tipo !== '' || $leida !== '') ? '/notificaciones' : '',
                'collapseId' => 'notificationFiltersCollapse',
                'toggleLabel' => 'Filtros',
                'applyLabel' => 'Aplicar',
                'clearLabel' => 'Limpiar',
                'searchConfig' => [
                    'fields' => [
                        [
                            'name' => 'q',
                            'type' => 'text',
                            'placeholder' => 'Buscar por titulo o usuario...',
                            'value' => $search,
                        ],
                    ],
                    'submitLabel' => 'Buscar',
                    'submitIcon' => 'fa fa-search',
                    'groupStyle' => 'max-width: 560px; width: 100%;',
                ],
                'filters' => [
                    [
                        'name' => 'modulo',
                        'label' => 'Módulo',
                        'value' => $modulo,
                        'allLabel' => 'Todos',
                        'options' => self::MODULO_OPTIONS,
                    ],
                    [
                        'name' => 'tipo',
                        'label' => 'Tipo',
                        'value' => $tipo,
                        'allLabel' => 'Todos',
                        'options' => self::TIPO_OPTIONS,
                    ],
                    [
                        'name' => 'leida',
                        'label' => 'Estado',
                        'value' => $leida,
                        'allLabel' => 'Todas',
                        'options' => self::LEIDA_OPTIONS,
                    ],
                ],
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // VER — auto-marca como leida al abrir el detalle
    // -------------------------------------------------------------------------

    public function ver(string $id): void
    {
        $this->requireAuth();
        $usuId = trim((string) (Auth::user()['id'] ?? ''));

        $item = null;
        try {
            $model = new NotificacionModel();
            $item  = $model->findById($id, $usuId);

            if (is_array($item) && (int) ($item['noti_leida'] ?? 0) === 0) {
                $model->marcarLeida($id, $usuId);
                $item['noti_leida']    = 1;
                $item['noti_leida_en'] = date('Y-m-d H:i:s');
            }
        } catch (Throwable) {
            $item = null;
        }

        if (!is_array($item)) {
            $this->redirect('/notificaciones');
            return;
        }

        $this->renderAdminModule('notificacion/ver', [
            'title'  => 'Detalle de notificacion',
            'user'   => Auth::user(),
            'item'   => $item,
            'itemId' => $id,
        ]);
    }

    // -------------------------------------------------------------------------
    // MARCAR LEIDA (POST — llamada via fetch/AJAX o formulario)
    // -------------------------------------------------------------------------

    public function marcarLeida(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $usuId = trim((string) (Auth::user()['id'] ?? ''));

        try {
            (new NotificacionModel())->marcarLeida($id, $usuId);
        } catch (Throwable) {
            // Silencioso: no es critico
        }

        $this->redirect('/notificaciones/' . urlencode($id) . '/ver');
    }
}
