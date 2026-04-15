<?php

namespace App\Controllers;

use App\Models\GrupoModel;
use Core\Auth;
use Core\Controller;
use Core\Validator;
use Throwable;

class GrupoController extends Controller
{
    // -------------------------------------------------------------------------
    // INDEX
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $this->requireAuth();

        $grupos     = [];
        $page       = $this->getCurrentPage();
        $perPage    = $this->getDefaultPerPage();
        $filters    = $this->getQueryParams(['q' => '']);
        $search     = (string) ($filters['q'] ?? '');
        $pagination = $this->buildPagination(0, $page, $perPage, '/grupos', ['q' => $search]);

        try {
            $model      = new GrupoModel();
            $totalRows  = $model->countAll($search);
            $pagination = $this->buildPagination($totalRows, $page, $perPage, '/grupos', ['q' => $search]);
            $grupos     = $model->paginate((int) $pagination['offset'], (int) $pagination['perPage'], $search);
        } catch (Throwable) {
            $grupos = [];
        }

        $this->renderAdminModule('grupo/index', [
            'title'        => 'Grupos',
            'user'         => Auth::user(),
            'grupos'       => $grupos,
            'pagination'   => $pagination,
            'search'       => $search,
            'searchConfig' => [
                'action'      => '/grupos',
                'method'      => 'GET',
                'fields'      => [
                    [
                        'name'        => 'q',
                        'type'        => 'text',
                        'placeholder' => 'Buscar por ID o descripcion...',
                        'value'       => $search,
                        'icon'        => 'fa fa-search',
                    ],
                ],
                'submitLabel' => 'Buscar',
                'submitIcon'  => 'fa fa-search',
                'clearUrl'    => $search !== '' ? '/grupos' : '',
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // AGREGAR
    // -------------------------------------------------------------------------

    public function agregar(): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new GrupoModel();

        $this->renderAdminModule('grupo/agregar', [
            'title'     => 'Nuevo grupo',
            'user'      => Auth::user(),
            'error'     => null,
            'elementos' => $model->getAllElementos(),
            'permisos'  => [],
            'form'      => [
                'gru_id'          => '',
                'gru_descripcion' => '',
                'gru_estado'      => 'H',
            ],
        ]);
    }

    public function guardar(): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model  = new GrupoModel();
        $params = $this->request->getParams();

        $validator = Validator::make($params, [
            'gru_id'          => 'required|string|min:1|max:100',
            'gru_descripcion' => 'required|string|min:2|max:250',
            'gru_estado'      => 'required|string|min:1|max:1',
        ], [
            'gru_id'          => 'ID de grupo',
            'gru_descripcion' => 'Descripcion',
            'gru_estado'      => 'Estado',
        ]);
        if (!$validator->passes()) {
            $this->renderAdminModule('grupo/agregar', [
                'title'     => 'Nuevo grupo',
                'user'      => Auth::user(),
                'error'     => $validator->first(),
                'errors'    => $validator->errors(),
                'elementos' => $model->getAllElementos(),
                'permisos'  => $this->parsePermisos($params),
                'form'      => $params,
            ]);
            return;
        }

        $gruId = trim((string) ($params['gru_id'] ?? ''));

        // Verificar unicidad del ID
        if ($model->existsById($gruId)) {
            $this->renderAdminModule('grupo/agregar', [
                'title'     => 'Nuevo grupo',
                'user'      => Auth::user(),
                'error'     => 'Ya existe un grupo con ese ID.',
                'errors'    => [],
                'elementos' => $model->getAllElementos(),
                'permisos'  => $this->parsePermisos($params),
                'form'      => $params,
            ]);
            return;
        }

        // Verificar unicidad de la descripcion
        $descripcion = trim((string) ($params['gru_descripcion'] ?? ''));
        if ($model->existsByDescripcion($descripcion)) {
            $this->renderAdminModule('grupo/agregar', [
                'title'     => 'Nuevo grupo',
                'user'      => Auth::user(),
                'error'     => 'Ya existe un grupo con esa descripcion.',
                'errors'    => [],
                'elementos' => $model->getAllElementos(),
                'permisos'  => $this->parsePermisos($params),
                'form'      => $params,
            ]);
            return;
        }

        try {
            $model->createGrupo($params);
            $this->logAction($model->getLastActionLog(), 'CREATE');

            // Sincronizar permisos
            $permisos = $this->parsePermisos($params);
            $model->syncPermisos($gruId, $permisos);

            $this->logAction(
                "SYNC_PERMISOS grupo={$gruId} pares=[" . implode(',', $permisos) . ']',
                'CREATE'
            );
        } catch (Throwable $e) {
            error_log('[GrupoController::guardar] ' . $e->getMessage());
            $this->renderAdminModule('grupo/agregar', [
                'title'     => 'Nuevo grupo',
                'user'      => Auth::user(),
                'error'     => 'Ocurrio un error al guardar el grupo. Intente nuevamente.',
                'errors'    => [],
                'elementos' => $model->getAllElementos(),
                'permisos'  => $this->parsePermisos($params),
                'form'      => $params,
            ]);
            return;
        }

        $this->invalidateMenuCache();
        $this->flashSuccess('Grupo registrado correctamente.');
        $this->redirect('/grupos');
    }

    // -------------------------------------------------------------------------
    // EDITAR
    // -------------------------------------------------------------------------

    public function editar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new GrupoModel();
        $grupo = $model->findById($id);
        if (!is_array($grupo)) {
            $this->redirect('/grupos');
            return;
        }

        $this->renderAdminModule('grupo/editar', [
            'title'    => 'Editar grupo',
            'user'     => Auth::user(),
            'error'    => null,
            'elementos' => $model->getAllElementos(),
            'permisos' => $model->getPermisos($id),
            'form'     => $grupo,
            'grupoId'  => $id,
        ]);
    }

    public function actualizar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new GrupoModel();
        $grupo = $model->findById($id);
        if (!is_array($grupo)) {
            $this->redirect('/grupos');
            return;
        }

        $params = $this->request->getParams();

        $validator = Validator::make($params, [
            'gru_descripcion' => 'required|string|min:2|max:250',
            'gru_estado'      => 'required|string|min:1|max:1',
        ], [
            'gru_descripcion' => 'Descripcion',
            'gru_estado'      => 'Estado',
        ]);
        if (!$validator->passes()) {
            $this->renderAdminModule('grupo/editar', [
                'title'    => 'Editar grupo',
                'user'     => Auth::user(),
                'error'    => $validator->first(),
                'errors'   => $validator->errors(),
                'elementos' => $model->getAllElementos(),
                'permisos' => $this->parsePermisos($params),
                'form'     => $params,
                'grupoId'  => $id,
            ]);
            return;
        }

        // Verificar unicidad de descripcion excluyendo el registro actual
        $descripcion = trim((string) ($params['gru_descripcion'] ?? ''));
        if ($model->existsByDescripcion($descripcion, $id)) {
            $this->renderAdminModule('grupo/editar', [
                'title'    => 'Editar grupo',
                'user'     => Auth::user(),
                'error'    => 'Ya existe otro grupo con esa descripcion.',
                'errors'   => [],
                'elementos' => $model->getAllElementos(),
                'permisos' => $this->parsePermisos($params),
                'form'     => $params,
                'grupoId'  => $id,
            ]);
            return;
        }

        try {
            $model->updateGrupo($id, $params);
            $this->logAction($model->getLastActionLog(), 'UPDATE');

            // Sincronizar permisos
            $permisos = $this->parsePermisos($params);
            $model->syncPermisos($id, $permisos);

            $this->logAction(
                "SYNC_PERMISOS grupo={$id} pares=[" . implode(',', $permisos) . ']',
                'UPDATE'
            );
        } catch (Throwable $e) {
            error_log('[GrupoController::actualizar] ' . $e->getMessage());
            $this->renderAdminModule('grupo/editar', [
                'title'    => 'Editar grupo',
                'user'     => Auth::user(),
                'error'    => 'Ocurrio un error al actualizar el grupo. Intente nuevamente.',
                'errors'   => [],
                'elementos' => $model->getAllElementos(),
                'permisos' => $this->parsePermisos($params),
                'form'     => $params,
                'grupoId'  => $id,
            ]);
            return;
        }

        $this->invalidateMenuCache();
        $this->flashSuccess('Grupo actualizado correctamente.');
        $this->redirect('/grupos');
    }

    // -------------------------------------------------------------------------
    // ELIMINAR
    // -------------------------------------------------------------------------

    public function eliminar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new GrupoModel();
        $grupo = $model->findById($id);
        if (!is_array($grupo)) {
            $this->redirect('/grupos');
            return;
        }

        $hasUsuarios = $model->hasUsuarios($id);
        $permisos    = $model->getPermisos($id);

        $this->renderAdminModule('grupo/eliminar', [
            'title'       => 'Eliminar grupo',
            'user'        => Auth::user(),
            'form'        => $grupo,
            'grupoId'     => $id,
            'hasUsuarios' => $hasUsuarios,
            'permisos'    => $permisos,
        ]);
    }

    public function borrar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new GrupoModel();
        $grupo = $model->findById($id);
        if (!is_array($grupo)) {
            $this->redirect('/grupos');
            return;
        }

        // Bloquear si tiene usuarios asignados
        if ($model->hasUsuarios($id)) {
            $this->flashError('No se puede eliminar el grupo porque tiene usuarios asignados.');
            $this->redirect('/grupos/' . urlencode($id) . '/eliminar');
            return;
        }

        try {
            // Eliminar permisos primero (FK)
            $model->syncPermisos($id, []);
            $this->logAction("DELETE_PERMISOS grupo={$id}", 'DELETE');

            $model->deleteGrupo($id);
            $this->logAction($model->getLastActionLog(), 'DELETE');
        } catch (Throwable $e) {
            error_log('[GrupoController::borrar] ' . $e->getMessage());
            $this->flashError('Ocurrio un error al eliminar el grupo. Intente nuevamente.');
            $this->redirect('/grupos/' . urlencode($id) . '/eliminar');
            return;
        }

        $this->invalidateMenuCache();
        $this->flashSuccess('Grupo eliminado correctamente.');
        $this->redirect('/grupos');
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Extrae y valida los pares "ele_id:tar_id" enviados por el formulario de permisos.
     *
     * @return string[]
     */
    private function parsePermisos(array $params): array
    {
        $raw = $params['permisos'] ?? [];
        if (!is_array($raw)) {
            return [];
        }
        $result = [];
        foreach ($raw as $item) {
            $parts = explode(':', (string) $item, 2);
            if (count($parts) !== 2) {
                continue;
            }
            $eleId = (int) $parts[0];
            $tarId = (int) $parts[1];
            if ($eleId > 0 && $tarId > 0) {
                $result[] = $eleId . ':' . $tarId;
            }
        }
        return $result;
    }
}
