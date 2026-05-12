<?php

namespace App\Controllers;

use App\Models\NotificacionModel;
use Core\Auth;
use Core\Controller;
use Throwable;

class NotificacionController extends Controller
{
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
        $filters = $this->getQueryParams(['q' => '', 'modulo' => '', 'leida' => '']);
        $search  = $filters['q'];
        $modulo  = $filters['modulo'];
        $leida   = $filters['leida'];

        $pagination = $this->buildPagination(0, $page, $perPage, '/notificaciones', [
            'q'      => $search,
            'modulo' => $modulo,
            'leida'  => $leida,
        ]);

        try {
            $model      = new NotificacionModel();
            $totalRows  = $model->countAll($search, $modulo, $leida, $usuId);
            $pagination = $this->buildPagination($totalRows, $page, $perPage, '/notificaciones', [
                'q'      => $search,
                'modulo' => $modulo,
                'leida'  => $leida,
            ]);
            $items = $model->paginate(
                (int) $pagination['offset'],
                (int) $pagination['perPage'],
                $search,
                $modulo,
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
            'leida'      => $leida,
            'searchConfig' => [
                'action'      => '/notificaciones',
                'method'      => 'GET',
                'fields'      => [
                    [
                        'name'        => 'q',
                        'type'        => 'text',
                        'placeholder' => 'Buscar por titulo o usuario...',
                        'value'       => $search,
                        'icon'        => 'fa fa-search',
                    ],
                ],
                'submitLabel' => 'Buscar',
                'submitIcon'  => 'fa fa-search',
                'clearUrl'    => ($search !== '' || $modulo !== '' || $leida !== '') ? '/notificaciones' : '',
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
