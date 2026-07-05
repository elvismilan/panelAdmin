<?php

namespace App\Controllers;

use App\Models\LogModel;
use Core\Auth;
use Core\Controller;
use Core\Filter\FilterBar;
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
        
        $queryParams = ['q' => $search];
        if ($tipo !== '') $queryParams['tipo'] = $tipo;
        
        $pagination = $this->buildPagination(0, $page, $perPage, '/logs', $queryParams);

        try {
            $model      = new LogModel();
            
            // Construir el FilterBar con tipos disponibles
            $tipoOptions = [];
            foreach (self::TIPOS as $val => $meta) {
                $tipoOptions[] = [
                    'value' => $val,
                    'label' => $meta['label']
                ];
            }
            $filterBar = FilterBar::make()
                ->chips('tipo', 'Tipo', $tipoOptions);
            
            $totalRows  = $model->countAll($search, $tipo);
            $pagination = $this->buildPagination($totalRows, $page, $perPage, '/logs', $queryParams);
            $logs       = $model->paginate((int) $pagination['offset'], (int) $pagination['perPage'], $search, $tipo);
        } catch (Throwable) {
            $logs = [];
            $filterBar = FilterBar::make()->chips('tipo', 'Tipo', []);
        }

        $this->renderAdminModule('log/index', [
            'title'      => 'Logs del sistema',
            'user'       => Auth::user(),
            'logs'       => $logs,
            'tipos'      => self::TIPOS,
            'pagination' => $pagination,
            'search'     => $search,
            'tipo'       => $tipo,
            'filterBarGroups' => $filterBar->toView($filters, '/logs'),
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
