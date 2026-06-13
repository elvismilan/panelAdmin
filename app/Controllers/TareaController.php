<?php

namespace App\Controllers;

use App\Models\TareaModel;
use Core\Auth;
use Core\Controller;
use Core\FlashMessages;
use Core\NotificacionService;
use Core\UiMessages;
use Core\ValidationMessages;
use Core\Validator;
use Throwable;

class TareaController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $user = Auth::user();
        $tareas = [];
        $page = $this->getCurrentPage();
        $perPage = $this->getDefaultPerPage();
        $filters = $this->getQueryParams(['q' => '']);
        $search = (string) ($filters['q'] ?? '');
        $pagination = $this->buildPagination(0, $page, $perPage, '/tareas', ['q' => $search]);

        try {
            $model = new TareaModel();
            $totalRows = $model->countAll($search);
            $pagination = $this->buildPagination($totalRows, $page, $perPage, '/tareas', ['q' => $search]);
            $tareas = $model->paginate((int) $pagination['offset'], (int) $pagination['perPage'], $search);
        } catch (Throwable) {
            $tareas = [];
        }

        $this->renderAdminModule('tarea/index', [
            'title' => UiMessages::TAREA_INDEX_TITLE,
            'user' => $user,
            'tareas' => $tareas,
            'pagination' => $pagination,
            'search' => $search,
            'searchConfig' => [
                'action' => '/tareas',
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
                'clearUrl' => $search !== '' ? '/tareas' : '',
            ],
        ]);
    }

    public function agregar(): void
    {
        $this->requireAuth();

        $this->renderAdminModule('tarea/agregar', [
            'title' => UiMessages::TAREA_CREATE_TITLE,
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
        $this->requireCsrf();

        $model = new TareaModel();
        $params = $this->request->getParams();
        $validator = Validator::make($params, [
            'tar_nombre' => 'required|string|min:2|max:120',
        ], [
            'tar_nombre' => 'Nombre',
        ]);
        $passes = $validator->passes();
        $nombre = (string) $validator->value('tar_nombre', '');

        if (!$passes) {
            $this->renderAdminModule('tarea/agregar', [
                'title' => UiMessages::TAREA_CREATE_TITLE,
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
                'title' => UiMessages::TAREA_CREATE_TITLE,
                'user' => Auth::user(),
                'error' => ValidationMessages::TAREA_ALREADY_EXISTS,
                'form' => [
                    'tar_nombre' => $nombre,
                ],
            ]);
            return;
        }

        $tarId = $model->createTask([
            'tar_nombre' => $nombre,
        ]);
        $this->logAction($model->getLastActionLog(), 'CREATE');
        NotificacionService::registrar('tareas', 'CREATE', (string) (Auth::user()['id'] ?? 'ANON'), $tarId);

        $this->invalidateMenuCache();
        $this->flashSuccess(FlashMessages::TAREA_CREATED);

        $this->redirect('/tareas');
    }

    public function editar(string $id): void
    {
        $this->requireAuth();

        $tarea = (new TareaModel())->findById($id);
        if (!is_array($tarea)) {
            $this->redirect('/tareas');
            return;
        }

        $this->renderAdminModule('tarea/editar', [
            'title' => UiMessages::TAREA_EDIT_TITLE,
            'user' => Auth::user(),
            'error' => null,
            'form' => $tarea,
            'tareaId' => $id,
        ]);
    }

    public function actualizar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new TareaModel();
        $params = $this->request->getParams();
        $validator = Validator::make($params, [
            'tar_nombre' => 'required|string|min:2|max:120',
        ], [
            'tar_nombre' => 'Nombre',
        ]);
        $passes = $validator->passes();
        $nombre = (string) $validator->value('tar_nombre', '');

        if (!$passes) {
            $this->renderAdminModule('tarea/editar', [
                'title' => UiMessages::TAREA_EDIT_TITLE,
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
                'title' => UiMessages::TAREA_EDIT_TITLE,
                'user' => Auth::user(),
                'error' => ValidationMessages::TAREA_ALREADY_EXISTS,
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
        $this->logAction($model->getLastActionLog(), 'UPDATE');
        NotificacionService::registrar('tareas', 'UPDATE', (string) (Auth::user()['id'] ?? 'ANON'), $id);

        $this->invalidateMenuCache();
        $this->flashSuccess(FlashMessages::TAREA_UPDATED);

        $this->redirect('/tareas');
    }

    public function eliminar(string $id): void
    {
        $this->requireAuth();

        $model = new TareaModel();
        $tarea = $model->findById($id);
        if (!is_array($tarea)) {
            $this->redirect('/tareas');
            return;
        }

        $this->renderAdminModule('tarea/eliminar', [
            'title'          => UiMessages::TAREA_DELETE_TITLE,
            'user'           => Auth::user(),
            'form'           => $tarea,
            'tareaId'        => $id,
            'linkedToModulo' => $model->isLinkedToModulo($id),
        ]);
    }

    public function borrar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new TareaModel();
        $tarea = $model->findById($id);
        if (!is_array($tarea)) {
            $this->redirect('/tareas');
            return;
        }

        if ($model->isLinkedToModulo($id)) {
            $this->flashError(FlashMessages::TAREA_DELETE_LINKED_FORBIDDEN);
            $this->redirect('/tareas/' . urlencode($id) . '/eliminar');
            return;
        }

        $nombre = (string) ($tarea['tar_nombre'] ?? '');
        $model->deleteTask($id);
        $this->logAction($model->getLastActionLog(), 'DELETE');
        NotificacionService::registrar('tareas', 'DELETE', (string) (Auth::user()['id'] ?? 'ANON'), $id);
        $this->invalidateMenuCache();
        $this->flashSuccess(FlashMessages::TAREA_DELETED);
        $this->redirect('/tareas');
    }
}
