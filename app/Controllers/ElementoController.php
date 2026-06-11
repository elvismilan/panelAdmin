<?php

namespace App\Controllers;

use App\Models\ElementoModel;
use Core\Helpers\IconHelper;
use Core\Auth;
use Core\Controller;
use Core\Filter\FilterBar;
use Core\FlashMessages;
use Core\NotificacionService;
use Core\UiMessages;
use Core\ValidationMessages;
use Core\Validator;
use Throwable;

class ElementoController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $user = Auth::user();
        $elementos = [];
        $page = $this->getCurrentPage();
        $perPage = $this->getDefaultPerPage();
        $filters = $this->getQueryParams(['q' => '', 'padre' => '']);
        $search = (string) ($filters['q'] ?? '');
        $padre = (string) ($filters['padre'] ?? '');
        
        $queryParams = ['q' => $search];
        if ($padre !== '') $queryParams['padre'] = $padre;
        
        $pagination = $this->buildPagination(0, $page, $perPage, '/modulos', $queryParams);

        try {
            $model = new ElementoModel();
            
            // Construir el FilterBar con opciones de padre
            $padreOptions = $model->getPadreFilterOptions();
            $filterBar = FilterBar::make()
                ->chips('padre', 'Padre', $padreOptions);
            
            $totalRows = $model->countAllWithParentFilter($search, $padre);
            $pagination = $this->buildPagination($totalRows, $page, $perPage, '/modulos', $queryParams);
            $elementos = $model->paginateWithParentFilter(
                (int) $pagination['offset'], 
                (int) $pagination['perPage'], 
                $search, 
                $padre
            );
        } catch (Throwable) {
            $elementos = [];
            $filterBar = FilterBar::make()
                ->chips('padre', 'Padre', []);
        }

        $this->renderAdminModule('elemento/index', [
            'title' => UiMessages::MODULO_INDEX_TITLE,
            'user' => $user,
            'elementos' => $elementos,
            'pagination' => $pagination,
            'search' => $search,
            'filterBarGroups' => $filterBar->toView($filters, '/modulos'),
            'searchConfig' => [
                'action' => '/modulos',
                'method' => 'GET',
                'fields' => [
                    [
                        'name' => 'q',
                        'type' => 'text',
                        'placeholder' => 'Buscar por nombre o titulo...',
                        'value' => $search,
                        'icon' => 'fa fa-search',
                    ],
                ],
                'submitLabel' => 'Buscar',
                'submitIcon' => 'fa fa-search',
                'clearUrl' => $search !== '' || $padre !== '' ? '/modulos' : '',
            ],
        ]);
    }

    public function agregar(): void
    {
        $this->requireAuth();

        $model = new ElementoModel();

        $this->renderAdminModule('elemento/agregar', [
            'title' => UiMessages::MODULO_CREATE_TITLE,
            'user' => Auth::user(),
            'error' => null,
            'padres' => $model->allForDropdown(),
            'iconOptions' => IconHelper::options(),
            'todasTareas' => $model->allTareas(),
            'tareasSeleccionadas' => [],
            'form' => [
                'ele_nombre' => '',
                'ele_titulo' => '',
                'ele_estado' => 'H',
                'ele_icono'  => '',
                'ele_orden'  => '',
                'ele_tipo'   => 'M',
                'ele_padre'  => '',
                'ele_tarea'  => 'ACCEDER',
            ],
        ]);
    }

    public function guardar(): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new ElementoModel();
        $params = $this->request->getParams();
        if ((string) ($params['ele_tipo'] ?? '') !== 'S') {
            $params['ele_padre'] = '';
        }
        $tareaIds = isset($params['tareas']) && is_array($params['tareas']) ? $params['tareas'] : [];

        $validator = Validator::make($params, [
            'ele_nombre' => 'required|string|min:2|max:250',
            'ele_titulo'  => 'required|string|min:2|max:100',
            'ele_estado'  => 'required|string|min:1|max:3',
            'ele_tipo'    => 'required|string|min:1|max:1',
            'ele_tarea'   => 'required|string|min:1|max:100',
            'ele_icono'   => 'nullable|string|max:250',
            'ele_orden'   => 'nullable',
            'ele_padre'   => 'nullable',
        ], [
            'ele_nombre' => 'Nombre',
            'ele_titulo'  => 'Slug',
            'ele_estado'  => 'Estado',
            'ele_tipo'    => 'Tipo',
            'ele_tarea'   => 'Tarea por defecto',
            'ele_icono'   => 'Icono',
            'ele_orden'   => 'Orden',
            'ele_padre'   => 'Modulo padre',
        ]);
        $passes = $validator->passes();

        $nombre = (string) $validator->value('ele_nombre', '');

        if (!$passes) {
            $this->renderAdminModule('elemento/agregar', [
                'title' => UiMessages::MODULO_CREATE_TITLE,
                'user' => Auth::user(),
                'error' => $validator->first(),
                'errors' => $validator->errors(),
                'padres' => $model->allForDropdown(),
                'iconOptions' => IconHelper::optionsWithSelected((string) ($params['ele_icono'] ?? '')),
                'todasTareas' => $model->allTareas(),
                'tareasSeleccionadas' => $tareaIds,
                'form' => $params,
            ]);
            return;
        }

        $iconClass = trim((string) ($params['ele_icono'] ?? ''));
        if ($iconClass !== '' && !IconHelper::isAllowed($iconClass)) {
            $this->renderAdminModule('elemento/agregar', [
                'title' => UiMessages::MODULO_CREATE_TITLE,
                'user' => Auth::user(),
                'error' => ValidationMessages::MODULO_ICON_INVALID,
                'padres' => $model->allForDropdown(),
                'iconOptions' => IconHelper::optionsWithSelected($iconClass),
                'todasTareas' => $model->allTareas(),
                'tareasSeleccionadas' => $tareaIds,
                'form' => $params,
            ]);
            return;
        }

        if ($model->existsByNombre($nombre)) {
            $this->renderAdminModule('elemento/agregar', [
                'title' => UiMessages::MODULO_CREATE_TITLE,
                'user' => Auth::user(),
                'error' => ValidationMessages::MODULO_ALREADY_EXISTS,
                'padres' => $model->allForDropdown(),
                'iconOptions' => IconHelper::optionsWithSelected((string) ($params['ele_icono'] ?? '')),
                'todasTareas' => $model->allTareas(),
                'tareasSeleccionadas' => $tareaIds,
                'form' => $params,
            ]);
            return;
        }

        $eleId = $model->createElemento($params, $tareaIds);
        $this->logAction($model->getLastActionLog(), 'CREATE');

        $this->flashSuccess(FlashMessages::MODULO_CREATED);
        $this->redirect('/modulos');
    }

    public function editar(string $id): void
    {
        $this->requireAuth();

        $model = new ElementoModel();
        $elemento = $model->findById($id);
        if (!is_array($elemento)) {
            $this->redirect('/modulos');
            return;
        }

        $this->renderAdminModule('elemento/editar', [
            'title' => UiMessages::MODULO_EDIT_TITLE,
            'user' => Auth::user(),
            'error' => null,
            'form' => $elemento,
            'elementoId' => $id,
            'padres' => $model->allForDropdown(),
            'iconOptions' => IconHelper::optionsWithSelected((string) ($elemento['ele_icono'] ?? '')),
            'todasTareas' => $model->allTareas(),
            'tareasSeleccionadas' => $model->getElementoTareaIds($id),
        ]);
    }

    public function actualizar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new ElementoModel();
        $elemento = $model->findById($id);
        if (!is_array($elemento)) {
            $this->redirect('/modulos');
            return;
        }

        $params = $this->request->getParams();
        if ((string) ($params['ele_tipo'] ?? '') !== 'S') {
            $params['ele_padre'] = '';
        }
        $tareaIds = isset($params['tareas']) && is_array($params['tareas']) ? $params['tareas'] : [];

        $validator = Validator::make($params, [
            'ele_nombre' => 'required|string|min:2|max:250',
            'ele_titulo'  => 'required|string|min:2|max:100',
            'ele_estado'  => 'required|string|min:1|max:3',
            'ele_tipo'    => 'required|string|min:1|max:1',
            'ele_tarea'   => 'required|string|min:1|max:100',
            'ele_icono'   => 'nullable|string|max:250',
            'ele_orden'   => 'nullable',
            'ele_padre'   => 'nullable',
        ], [
            'ele_nombre' => 'Nombre',
            'ele_titulo'  => 'Slug',
            'ele_estado'  => 'Estado',
            'ele_tipo'    => 'Tipo',
            'ele_tarea'   => 'Tarea por defecto',
            'ele_icono'   => 'Icono',
            'ele_orden'   => 'Orden',
            'ele_padre'   => 'Modulo padre',
        ]);
        $passes = $validator->passes();

        $nombre = (string) $validator->value('ele_nombre', '');

        if (!$passes) {
            $this->renderAdminModule('elemento/editar', [
                'title' => UiMessages::MODULO_EDIT_TITLE,
                'user' => Auth::user(),
                'error' => $validator->first(),
                'errors' => $validator->errors(),
                'form' => $params,
                'elementoId' => $id,
                'padres' => $model->allForDropdown(),
                'iconOptions' => IconHelper::optionsWithSelected((string) ($params['ele_icono'] ?? '')),
                'todasTareas' => $model->allTareas(),
                'tareasSeleccionadas' => $tareaIds,
            ]);
            return;
        }

        $iconClass = trim((string) ($params['ele_icono'] ?? ''));
        if ($iconClass !== '' && !IconHelper::isAllowed($iconClass)) {
            $this->renderAdminModule('elemento/editar', [
                'title' => UiMessages::MODULO_EDIT_TITLE,
                'user' => Auth::user(),
                'error' => ValidationMessages::MODULO_ICON_INVALID,
                'form' => $params,
                'elementoId' => $id,
                'padres' => $model->allForDropdown(),
                'iconOptions' => IconHelper::optionsWithSelected($iconClass),
                'todasTareas' => $model->allTareas(),
                'tareasSeleccionadas' => $tareaIds,
            ]);
            return;
        }

        if ($model->existsByNombre($nombre, $id)) {
            $this->renderAdminModule('elemento/editar', [
                'title' => UiMessages::MODULO_EDIT_TITLE,
                'user' => Auth::user(),
                'error' => ValidationMessages::MODULO_ALREADY_EXISTS,
                'form' => $params,
                'elementoId' => $id,
                'padres' => $model->allForDropdown(),
                'iconOptions' => IconHelper::optionsWithSelected((string) ($params['ele_icono'] ?? '')),
                'todasTareas' => $model->allTareas(),
                'tareasSeleccionadas' => $tareaIds,
            ]);
            return;
        }

        $model->updateElemento($id, $params, $tareaIds);
        $this->logAction($model->getLastActionLog(), 'UPDATE');

        $this->flashSuccess(FlashMessages::MODULO_UPDATED);
        $this->redirect('/modulos');
    }

    public function eliminar(string $id): void
    {
        $this->requireAuth();

        $model   = new ElementoModel();
        $elemento = $model->findById($id);
        if (!is_array($elemento)) {
            $this->redirect('/modulos');
            return;
        }

        $this->renderAdminModule('elemento/eliminar', [
            'title'             => UiMessages::MODULO_DELETE_TITLE,
            'user'              => Auth::user(),
            'form'              => $elemento,
            'elementoId'        => $id,
            'linkedToPermiso'   => $model->isLinkedToPermiso($id),
        ]);
    }

    public function borrar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model    = new ElementoModel();
        $elemento = $model->findById($id);
        if (!is_array($elemento)) {
            $this->redirect('/modulos');
            return;
        }

        if ($model->isLinkedToPermiso($id)) {
            $this->flashError(FlashMessages::MODULO_DELETE_LINKED_FORBIDDEN);
            $this->redirect('/modulos/' . urlencode($id) . '/eliminar');
            return;
        }

        $nombre = (string) ($elemento['ele_nombre'] ?? '');
        $model->deleteElemento($id);
        $this->logAction($model->getLastActionLog(), 'DELETE');
        NotificacionService::registrar('modulos', 'DELETE', (string) (Auth::user()['id'] ?? 'ANON'), $id);

        $this->flashSuccess(FlashMessages::MODULO_DELETED);
        $this->redirect('/modulos');
    }
}
