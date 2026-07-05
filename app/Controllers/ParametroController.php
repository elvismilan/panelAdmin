<?php

namespace App\Controllers;

use App\Models\ParametroModel;
use Core\Auth;
use Core\Controller;
use Core\Filter\FilterBar;
use Core\NotificacionService;
use Core\Validator;
use Throwable;

class ParametroController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $user    = Auth::user();
        $items   = [];
        $page    = $this->getCurrentPage();
        $perPage = $this->getDefaultPerPage();
        $filters = $this->getQueryParams(['q' => '', 'grupo' => '']);
        $search  = $filters['q'];
        $grupo   = $filters['grupo'];
        
        $queryParams = ['q' => $search];
        if ($grupo !== '') $queryParams['grupo'] = $grupo;
        
        $pagination = $this->buildPagination(0, $page, $perPage, '/parametros', $queryParams);

        try {
            $model = new ParametroModel();
            
            // Construir el FilterBar con grupos disponibles
            $grupoOptions = $model->getGrupoFilterOptions();
            $filterBar = FilterBar::make()
                ->chips('grupo', 'Grupo', $grupoOptions);
            
            $totalRows  = $model->countAllWithFilter($search, $grupo);
            $pagination = $this->buildPagination($totalRows, $page, $perPage, '/parametros', $queryParams);
            $items      = $model->paginateWithFilter((int) $pagination['offset'], (int) $pagination['perPage'], $search, $grupo);
        } catch (Throwable) {
            $items = [];
            $filterBar = FilterBar::make()->chips('grupo', 'Grupo', []);
        }

        $this->renderAdminModule('parametro/index', [
            'title'      => 'Parámetros del Sistema',
            'user'       => $user,
            'items'      => $items,
            'pagination' => $pagination,
            'search'     => $search,
            'filterBarGroups' => $filterBar->toView($filters, '/parametros'),
            'searchConfig' => [
                'action'      => '/parametros',
                'method'      => 'GET',
                'fields'      => [[
                    'name'        => 'q',
                    'type'        => 'text',
                    'placeholder' => 'Buscar por clave o etiqueta...',
                    'value'       => $search,
                    'icon'        => 'fa fa-search',
                ]],
                'submitLabel' => 'Buscar',
                'submitIcon'  => 'fa fa-search',
                'clearUrl'    => $search !== '' || $grupo !== '' ? '/parametros' : '',
            ],
        ]);
    }

    public function agregar(): void
    {
        $this->requireAuth();

        $this->renderAdminModule('parametro/agregar', [
            'title' => 'Nuevo Parámetro',
            'user'  => Auth::user(),
            'error' => null,
            'form'  => [
                'par_clave'  => '',
                'par_valor'  => '',
                'par_tipo'   => 'string',
                'par_grupo'  => '',
                'par_label'  => '',
            ],
        ]);
    }

    public function guardar(): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model  = new ParametroModel();
        $params = $this->request->getParams();

        $validator = Validator::make($params, [
            'par_clave'  => 'required|string|min:2|max:100',
            'par_valor'  => 'nullable|string',
            'par_tipo'   => 'required|string|min:1|max:50',
            'par_grupo'  => 'required|string|min:2|max:100',
            'par_label'  => 'required|string|min:2|max:250',
        ], [
            'par_clave'  => 'Clave',
            'par_valor'  => 'Valor',
            'par_tipo'   => 'Tipo',
            'par_grupo'  => 'Grupo',
            'par_label'  => 'Etiqueta',
        ]);

        $passes = $validator->passes();
        $clave = (string) $validator->value('par_clave', '');

        if (!$passes) {
            $this->renderAdminModule('parametro/agregar', [
                'title'  => 'Nuevo Parámetro',
                'user'   => Auth::user(),
                'error'  => $validator->first(),
                'errors' => $validator->errors(),
                'form'   => $params,
            ]);
            return;
        }

        if ($model->existsByClave($clave)) {
            $this->renderAdminModule('parametro/agregar', [
                'title' => 'Nuevo Parámetro',
                'user'  => Auth::user(),
                'error' => 'Ya existe un parámetro con esa clave.',
                'form'  => $params,
            ]);
            return;
        }

        $parId = $model->createRecord($params);
        $this->logAction($model->getLastActionLog(), 'CREATE');
        NotificacionService::registrar('parametros', 'CREATE', (string) (Auth::user()['id'] ?? 'ANON'), $parId);
        $this->flashSuccess('Parámetro creado correctamente.');
        $this->redirect('/parametros');
    }

    public function editar(string $id): void
    {
        $this->requireAuth();

        $item = (new ParametroModel())->findById($id);
        if (!is_array($item)) {
            $this->redirect('/parametros');
            return;
        }

        $this->renderAdminModule('parametro/editar', [
            'title'  => 'Editar Parámetro',
            'user'   => Auth::user(),
            'error'  => null,
            'form'   => $item,
            'itemId' => $id,
        ]);
    }

    public function actualizar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model  = new ParametroModel();
        $params = $this->request->getParams();

        $validator = Validator::make($params, [
            'par_clave'  => 'required|string|min:2|max:100',
            'par_valor'  => 'nullable|string',
            'par_tipo'   => 'required|string|min:1|max:50',
            'par_grupo'  => 'required|string|min:2|max:100',
            'par_label'  => 'required|string|min:2|max:250',
        ], [
            'par_clave'  => 'Clave',
            'par_valor'  => 'Valor',
            'par_tipo'   => 'Tipo',
            'par_grupo'  => 'Grupo',
            'par_label'  => 'Etiqueta',
        ]);

        $passes = $validator->passes();
        $clave  = (string) $validator->value('par_clave', '');

        if (!$passes) {
            $this->renderAdminModule('parametro/editar', [
                'title'  => 'Editar Parámetro',
                'user'   => Auth::user(),
                'error'  => $validator->first(),
                'errors' => $validator->errors(),
                'form'   => $params,
                'itemId' => $id,
            ]);
            return;
        }

        if ($model->existsByClave($clave, $id)) {
            $this->renderAdminModule('parametro/editar', [
                'title'  => 'Editar Parámetro',
                'user'   => Auth::user(),
                'error'  => 'Ya existe otro parámetro con esa clave.',
                'form'   => $params,
                'itemId' => $id,
            ]);
            return;
        }

        $model->updateRecord($id, $params);
        $this->logAction($model->getLastActionLog(), 'UPDATE');
        NotificacionService::registrar('parametros', 'UPDATE', (string) (Auth::user()['id'] ?? 'ANON'), $id);
        $this->flashSuccess('Parámetro actualizado correctamente.');
        $this->redirect('/parametros');
    }

    public function eliminar(string $id): void
    {
        $this->requireAuth();

        $model = new ParametroModel();
        $item  = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/parametros');
            return;
        }

        $this->renderAdminModule('parametro/eliminar', [
            'title'  => 'Eliminar Parámetro',
            'user'   => Auth::user(),
            'form'   => $item,
            'itemId' => $id,
        ]);
    }

    public function borrar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new ParametroModel();
        $item  = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/parametros');
            return;
        }

        $model->deleteRecord($id);
        $this->logAction($model->getLastActionLog(), 'DELETE');
        NotificacionService::registrar('parametros', 'DELETE', (string) (Auth::user()['id'] ?? 'ANON'), $id);

        $this->flashSuccess('Parámetro eliminado correctamente.');
        $this->redirect('/parametros');
    }
}
