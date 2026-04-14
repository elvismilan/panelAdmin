<?php

namespace App\Controllers;

use App\Models\ElementoModel;
use Core\Auth;
use Core\Controller;
use Core\Validator;
use Throwable;

class ElementoController extends Controller
{
    private function renderAdminModule(string $moduleView, array $data = []): void
    {
        $data['moduleView'] = $moduleView;
        $this->render($this->resolveTemplate('admin', 'layout'), $data);
    }

    public function index(): void
    {
        $this->requireAuth();

        $user = Auth::user();
        $elementos = [];
        $page = $this->getCurrentPage();
        $perPage = $this->getDefaultPerPage();
        $filters = $this->getQueryParams(['q' => '']);
        $search = (string) ($filters['q'] ?? '');
        $pagination = $this->buildPagination(0, $page, $perPage, '/admin/modulos', ['q' => $search]);

        try {
            $model = new ElementoModel();
            $totalRows = $model->countAll($search);
            $pagination = $this->buildPagination($totalRows, $page, $perPage, '/admin/modulos', ['q' => $search]);
            $elementos = $model->paginate((int) $pagination['offset'], (int) $pagination['perPage'], $search);
        } catch (Throwable) {
            $elementos = [];
        }

        $this->renderAdminModule('elemento/index', [
            'title' => 'Modulos',
            'user' => $user,
            'elementos' => $elementos,
            'pagination' => $pagination,
            'search' => $search,
            'searchConfig' => [
                'action' => '/admin/modulos',
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
                'clearUrl' => $search !== '' ? '/admin/modulos' : '',
            ],
        ]);
    }

    public function agregar(): void
    {
        $this->requireAuth();

        $model = new ElementoModel();

        $this->renderAdminModule('elemento/agregar', [
            'title' => 'Nuevo modulo',
            'user' => Auth::user(),
            'error' => null,
            'padres' => $model->allForDropdown(),
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

        $model = new ElementoModel();
        $params = $this->request->getParams();
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
        $validator->passes();

        $nombre = (string) $validator->value('ele_nombre', '');

        if ($validator->first() !== null) {
            $this->renderAdminModule('elemento/agregar', [
                'title' => 'Nuevo modulo',
                'user' => Auth::user(),
                'error' => $validator->first(),
                'errors' => $validator->errors(),
                'padres' => $model->allForDropdown(),
                'todasTareas' => $model->allTareas(),
                'tareasSeleccionadas' => $tareaIds,
                'form' => $params,
            ]);
            return;
        }

        if ($model->existsByNombre($nombre)) {
            $this->renderAdminModule('elemento/agregar', [
                'title' => 'Nuevo modulo',
                'user' => Auth::user(),
                'error' => 'Ya existe un modulo con ese nombre.',
                'padres' => $model->allForDropdown(),
                'todasTareas' => $model->allTareas(),
                'tareasSeleccionadas' => $tareaIds,
                'form' => $params,
            ]);
            return;
        }

        $eleId = $model->createElemento($params, $tareaIds);
        $this->logAction($model->getLastSqlLog(), 'CREATE');

        $this->flashSuccess('Modulo creado correctamente.');
        $this->redirect('/admin/modulos');
    }

    public function editar(string $id): void
    {
        $this->requireAuth();

        $model = new ElementoModel();
        $elemento = $model->findById($id);
        if (!is_array($elemento)) {
            $this->redirect('/admin/modulos');
            return;
        }

        $this->renderAdminModule('elemento/editar', [
            'title' => 'Editar modulo',
            'user' => Auth::user(),
            'error' => null,
            'form' => $elemento,
            'elementoId' => $id,
            'padres' => $model->allForDropdown(),
            'todasTareas' => $model->allTareas(),
            'tareasSeleccionadas' => $model->getElementoTareaIds($id),
        ]);
    }

    public function actualizar(string $id): void
    {
        $this->requireAuth();

        $model = new ElementoModel();
        $elemento = $model->findById($id);
        if (!is_array($elemento)) {
            $this->redirect('/admin/modulos');
            return;
        }

        $params = $this->request->getParams();
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
        $validator->passes();

        $nombre = (string) $validator->value('ele_nombre', '');

        if ($validator->first() !== null) {
            $this->renderAdminModule('elemento/editar', [
                'title' => 'Editar modulo',
                'user' => Auth::user(),
                'error' => $validator->first(),
                'errors' => $validator->errors(),
                'form' => $params,
                'elementoId' => $id,
                'padres' => $model->allForDropdown(),
                'todasTareas' => $model->allTareas(),
                'tareasSeleccionadas' => $tareaIds,
            ]);
            return;
        }

        if ($model->existsByNombre($nombre, $id)) {
            $this->renderAdminModule('elemento/editar', [
                'title' => 'Editar modulo',
                'user' => Auth::user(),
                'error' => 'Ya existe un modulo con ese nombre.',
                'form' => $params,
                'elementoId' => $id,
                'padres' => $model->allForDropdown(),
                'todasTareas' => $model->allTareas(),
                'tareasSeleccionadas' => $tareaIds,
            ]);
            return;
        }

        $model->updateElemento($id, $params, $tareaIds);
        $this->logAction($model->getLastSqlLog(), 'UPDATE');

        $this->flashSuccess('Modulo actualizado correctamente.');
        $this->redirect('/admin/modulos');
    }

    public function eliminar(string $id): void
    {
        $this->requireAuth();

        $model   = new ElementoModel();
        $elemento = $model->findById($id);
        if (!is_array($elemento)) {
            $this->redirect('/admin/modulos');
            return;
        }

        $this->renderAdminModule('elemento/eliminar', [
            'title'             => 'Eliminar modulo',
            'user'              => Auth::user(),
            'form'              => $elemento,
            'elementoId'        => $id,
            'linkedToPermiso'   => $model->isLinkedToPermiso($id),
        ]);
    }

    public function borrar(string $id): void
    {
        $this->requireAuth();

        $model    = new ElementoModel();
        $elemento = $model->findById($id);
        if (!is_array($elemento)) {
            $this->redirect('/admin/modulos');
            return;
        }

        if ($model->isLinkedToPermiso($id)) {
            $this->flashError('No se puede eliminar el modulo porque tiene permisos asignados. Elimine primero los permisos asociados.');
            $this->redirect('/admin/modulos/' . urlencode($id) . '/eliminar');
            return;
        }

        $nombre = (string) ($elemento['ele_nombre'] ?? '');
        $model->deleteElemento($id);
        $this->logAction($model->getLastSqlLog(), 'DELETE');

        $this->flashSuccess('Modulo eliminado correctamente.');
        $this->redirect('/admin/modulos');
    }
}
