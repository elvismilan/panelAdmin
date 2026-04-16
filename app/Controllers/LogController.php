<?php

namespace App\Controllers;

use App\Models\LogModel;
use Core\Auth;
use Core\Controller;
use Throwable;

class LogController extends Controller
{
    private const TIPOS = [
        'CREATE'     => ['label' => 'Creación',    'badge' => 'success'],
        'UPDATE'     => ['label' => 'Actualización','badge' => 'warning'],
        'DELETE'     => ['label' => 'Eliminación',  'badge' => 'danger'],
        'GENERAL'    => ['label' => 'General',      'badge' => 'secondary'],
        'AUTHZ_DENY' => ['label' => 'Acceso denegado', 'badge' => 'dark'],
    ];

    public function index(): void
    {
        $this->requireAuth();

        $logs       = [];
        $page       = $this->getCurrentPage();
        $perPage    = $this->getDefaultPerPage();
        $filters    = $this->getQueryParams(['q' => '', 'tipo' => '']);
        $search     = $filters['q'];
        $tipo       = $filters['tipo'];
        $pagination = $this->buildPagination(0, $page, $perPage, '/logs', ['q' => $search, 'tipo' => $tipo]);

        try {
            $model      = new LogModel();
            $totalRows  = $model->countAll($search, $tipo);
            $pagination = $this->buildPagination($totalRows, $page, $perPage, '/logs', ['q' => $search, 'tipo' => $tipo]);
            $logs       = $model->paginate((int) $pagination['offset'], (int) $pagination['perPage'], $search, $tipo);
        } catch (Throwable) {
            $logs = [];
        }

        $tipoOptions = [['value' => '', 'label' => 'Todos los tipos']];
        foreach (self::TIPOS as $val => $meta) {
            $tipoOptions[] = ['value' => $val, 'label' => $meta['label']];
        }

        $this->renderAdminModule('log/index', [
            'title'      => 'Logs del sistema',
            'user'       => Auth::user(),
            'logs'       => $logs,
            'tipos'      => self::TIPOS,
            'pagination' => $pagination,
            'search'     => $search,
            'tipo'       => $tipo,
            'searchConfig' => [
                'action'      => '/logs',
                'method'      => 'GET',
                'fields'      => [
                    [
                        'name'        => 'q',
                        'type'        => 'text',
                        'placeholder' => 'Buscar por acción, usuario o IP...',
                        'value'       => $search,
                        'icon'        => 'fa fa-search',
                    ],
                    [
                        'name'    => 'tipo',
                        'type'    => 'select',
                        'value'   => $tipo,
                        'options' => $tipoOptions,
                    ],
                ],
                'submitLabel' => 'Buscar',
                'submitIcon'  => 'fa fa-search',
                'clearUrl'    => ($search !== '' || $tipo !== '') ? '/logs' : '',
            ],
        ]);
    }

    public function ver(string $id): void
    {
        $this->requireAuth();

        $log = null;
        try {
            $log = (new LogModel())->findById($id);
        } catch (Throwable) {
            $log = null;
        }

        if (!is_array($log)) {
            $this->redirect('/logs');
            return;
        }

        $this->renderAdminModule('log/ver', [
            'title' => 'Detalle del log',
            'user'  => Auth::user(),
            'log'   => $log,
            'tipos' => self::TIPOS,
            'logId' => $id,
        ]);
    }
}
