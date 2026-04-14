<?php

namespace App\Controllers;

use App\Models\TareaModel;
use Core\Auth;
use Core\Controller;
use Core\Validator;
use Throwable;

class TareaController extends Controller
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
        $tareas = [];
        $page = $this->getCurrentPage();
        $perPage = $this->getDefaultPerPage();
        $filters = $this->getQueryParams(['q' => '']);
        $search = (string) ($filters['q'] ?? '');
        $pagination = $this->buildPagination(0, $page, $perPage, '/admin/tareas', ['q' => $search]);

        try {
            $model = new TareaModel();
            $totalRows = $model->countAll($search);
            $pagination = $this->buildPagination($totalRows, $page, $perPage, '/admin/tareas', ['q' => $search]);
            $tareas = $model->paginate((int) $pagination['offset'], (int) $pagination['perPage'], $search);
        } catch (Throwable) {
            $tareas = [];
        }

        $this->renderAdminModule('tarea/index', [
            'title' => 'Tareas',
            'user' => $user,
            'tareas' => $tareas,
            'pagination' => $pagination,
            'search' => $search,
            'searchConfig' => [
                'action' => '/admin/tareas',
                'method' => 'GET',
                'fields' => [
                    [
                        'name' => 'q',
                        'type' => 'text',
                        'placeholder' => 'Buscar por nombre...',
                        'value' => $search,
                        'icon' => 'fa fa-search',
                    ],
                ],
                'submitLabel' => 'Buscar',
                'submitIcon' => 'fa fa-search',
                'clearUrl' => $search !== '' ? '/admin/tareas' : '',
            ],
        ]);
    }

    public function agregar(): void
    {
        $this->requireAuth();

        $this->renderAdminModule('tarea/agregar', [
            'title' => 'Nueva tarea',
            'user' => Auth::user(),
            'error' => null,
            'form' => [
                'tar_nombre' => '',
            ],
        ]);
    }

    public function guardar(): void
    {
        $this->requireAuth();

        $model = new TareaModel();
        $params = $this->request->getParams();
        $validator = Validator::make($params, [
            'tar_nombre' => 'required|string|min:2|max:120',
        ], [
            'tar_nombre' => 'Nombre',
        ]);
        $validator->passes();
        $nombre = (string) $validator->value('tar_nombre', '');

        if ($validator->first() !== null) {
            $this->renderAdminModule('tarea/agregar', [
                'title' => 'Nueva tarea',
                'user' => Auth::user(),
                'error' => $validator->first(),
                'errors' => $validator->errors(),
                'form' => [
                    'tar_nombre' => $nombre,
                ],
            ]);
            return;
        }

        if ($model->existsByName($nombre)) {
            $this->renderAdminModule('tarea/agregar', [
                'title' => 'Nueva tarea',
                'user' => Auth::user(),
                'error' => 'Ya existe una tarea con ese nombre.',
                'form' => [
                    'tar_nombre' => $nombre,
                ],
            ]);
            return;
        }

        $tarId = $model->createTask([
            'tar_nombre' => $nombre,
        ]);
        $this->logAction($model->getLastSqlLog(), 'CREATE');

        $this->flashSuccess('Tarea creada correctamente.');

        $this->redirect('/admin/tareas');
    }

    public function editar(string $id): void
    {
        $this->requireAuth();

        $tarea = (new TareaModel())->findById($id);
        if (!is_array($tarea)) {
            $this->redirect('/admin/tareas');
            return;
        }

        $this->renderAdminModule('tarea/editar', [
            'title' => 'Editar tarea',
            'user' => Auth::user(),
            'error' => null,
            'form' => $tarea,
            'tareaId' => $id,
        ]);
    }

    public function actualizar(string $id): void
    {
        $this->requireAuth();

        $model = new TareaModel();
        $params = $this->request->getParams();
        $validator = Validator::make($params, [
            'tar_nombre' => 'required|string|min:2|max:120',
        ], [
            'tar_nombre' => 'Nombre',
        ]);
        $validator->passes();
        $nombre = (string) $validator->value('tar_nombre', '');

        if ($validator->first() !== null) {
            $this->renderAdminModule('tarea/editar', [
                'title' => 'Editar tarea',
                'user' => Auth::user(),
                'error' => $validator->first(),
                'errors' => $validator->errors(),
                'form' => [
                    'tar_nombre' => $nombre,
                ],
                'tareaId' => $id,
            ]);
            return;
        }

        if ($model->existsByName($nombre, $id)) {
            $this->renderAdminModule('tarea/editar', [
                'title' => 'Editar tarea',
                'user' => Auth::user(),
                'error' => 'Ya existe una tarea con ese nombre.',
                'form' => [
                    'tar_nombre' => $nombre,
                ],
                'tareaId' => $id,
            ]);
            return;
        }

        $model->updateTask($id, [
            'tar_nombre' => $nombre,
        ]);
        $this->logAction($model->getLastSqlLog(), 'UPDATE');

        $this->flashSuccess('Tarea actualizada correctamente.');

        $this->redirect('/admin/tareas');
    }

    public function eliminar(string $id): void
    {
        $this->requireAuth();

        $model = new TareaModel();
        $tarea = $model->findById($id);
        if (!is_array($tarea)) {
            $this->redirect('/admin/tareas');
            return;
        }

        $this->renderAdminModule('tarea/eliminar', [
            'title'          => 'Eliminar tarea',
            'user'           => Auth::user(),
            'form'           => $tarea,
            'tareaId'        => $id,
            'linkedToModulo' => $model->isLinkedToModulo($id),
        ]);
    }

    public function borrar(string $id): void
    {
        $this->requireAuth();

        $model = new TareaModel();
        $tarea = $model->findById($id);
        if (!is_array($tarea)) {
            $this->redirect('/admin/tareas');
            return;
        }

        if ($model->isLinkedToModulo($id)) {
            $this->flashError('No se puede eliminar la tarea porque esta asociada a uno o mas modulos. Desvincule la tarea de los modulos antes de continuar.');
            $this->redirect('/admin/tareas/' . urlencode($id) . '/eliminar');
            return;
        }

        $nombre = (string) ($tarea['tar_nombre'] ?? '');
        $model->deleteTask($id);
        $this->logAction($model->getLastSqlLog(), 'DELETE');

        $this->flashSuccess('Tarea eliminada correctamente.');
        $this->redirect('/admin/tareas');
    }
}
