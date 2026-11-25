<?php

namespace App\Controllers;

use App\Models\ContenidosModel;
use PanelAdmin\Core\Auth;
use PanelAdmin\Core\Concerns\HandlesImageUploads;
use PanelAdmin\Core\Controller;
use App\Messages\FlashMessages;
use PanelAdmin\Core\NotificacionService;
use App\Messages\UiMessages;
use PanelAdmin\Core\Validator;

class ContenidosController extends Controller
{
    use HandlesImageUploads;

    private const UPLOAD_MODULE = 'contenidos';
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const MAX_FILE_SIZE = 3145728; // 3 MB
    private const THUMB_SIZES = [
        ['w' => 150, 'h' => 150, 'mode' => 'crop'],
    ];

    private const SUMMERNOTE_CSS = '/assets/theme-one/css/summernote.css';
    private const SUMMERNOTE_JS = '/assets/theme-one/js/editor/summernote/summernote.js';
    private const FORM_INIT_JS = '/assets/js/contenidos-form.js';

    public function index(): void
    {
        $this->requireAuth();

        $list = $this->searchableIndex('/contenidos', ContenidosModel::class, 'Buscar por nombre, subtitulo, lugar o link...');

        $this->renderAdminModule('contenidos/index', [
            'title' => UiMessages::CONTENIDOS_INDEX_TITLE,
            'user' => Auth::user(),
            'items' => $list['items'],
            'pagination' => $list['pagination'],
            'search' => $list['search'],
            'searchConfig' => $list['searchConfig'],
        ]);
    }

    public function agregar(): void
    {
        $this->requireAuth();
        $this->enqueueFormAssets();

        $this->renderCreateForm($this->defaultForm());
    }

    public function guardar(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $this->enqueueFormAssets();

        $params = $this->request->getParams();
        if (trim((string) ($params['estado'] ?? '')) === '') {
            $params['estado'] = 'H';
        }

        [$passes, $error, $errors] = $this->validateForm($params);
        if (!$passes) {
            $this->renderCreateForm($this->mergeWithDefaults($params), $error, $errors);
            return;
        }

        $upload = $this->makeUploader()->handle('imagen');
        if ($upload->hasError()) {
            $this->renderCreateForm($this->mergeWithDefaults($params), $upload->getError());
            return;
        }

        $params['imagen'] = $this->extractImageName($upload->getPath());

        $model = new ContenidosModel();
        $createdId = $model->createRecord($params);
        $this->logAction($model->getLastActionLog(), 'CREATE');
        NotificacionService::registrar('contenidos', 'CREATE', (string) (Auth::user()['id'] ?? 'ANON'), $createdId);

        $this->flashSuccess(FlashMessages::CONTENIDOS_CREATED);
        $this->redirect('/contenidos');
    }

    public function editar(string $id): void
    {
        $this->requireAuth();
        $this->enqueueFormAssets();

        $item = (new ContenidosModel())->findById($id);
        if (!is_array($item)) {
            $this->redirect('/contenidos');
            return;
        }

        $item['imagen'] = $this->buildImagePreviewPath((string) ($item['imagen'] ?? ''));

        $this->renderEditForm($id, $this->mergeWithDefaults($item));
    }

    public function actualizar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $this->enqueueFormAssets();

        $model = new ContenidosModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/contenidos');
            return;
        }

        $storedImageName = $this->extractImageName((string) ($item['imagen'] ?? ''));
        $previewImagePath = $this->buildImagePreviewPath($storedImageName);

        $params = $this->request->getParams();
        [$passes, $error, $errors] = $this->validateForm($params);
        if (!$passes) {
            $params['imagen'] = $previewImagePath;
            $this->renderEditForm($id, $this->mergeWithDefaults($params), $error, $errors);
            return;
        }

        $imageName = $storedImageName;
        $upload = $this->makeUploader()->handle('imagen');
        if ($upload->hasError()) {
            $params['imagen'] = $previewImagePath;
            $this->renderEditForm($id, $this->mergeWithDefaults($params), $upload->getError());
            return;
        }

        if (!$upload->isEmpty()) {
            $imageName = $this->extractImageName($upload->getPath());
        }

        $params['imagen'] = $imageName;
        $model->updateRecord($id, $params);
        $this->logAction($model->getLastActionLog(), 'UPDATE');
        NotificacionService::registrar('contenidos', 'UPDATE', (string) (Auth::user()['id'] ?? 'ANON'), $id);

        $this->flashSuccess(FlashMessages::CONTENIDOS_UPDATED);
        $this->redirect('/contenidos');
    }

    public function eliminar(string $id): void
    {
        $this->requireAuth();

        $item = (new ContenidosModel())->findById($id);
        if (!is_array($item)) {
            $this->redirect('/contenidos');
            return;
        }

        $this->renderAdminModule('contenidos/eliminar', [
            'title' => UiMessages::CONTENIDOS_DELETE_TITLE,
            'user' => Auth::user(),
            'form' => $item,
            'itemId' => $id,
        ]);
    }

    public function borrar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new ContenidosModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/contenidos');
            return;
        }

        $imageName = $this->extractImageName((string) ($item['imagen'] ?? ''));
        if ($imageName !== '') {
            $this->deleteUploadedFiles(self::UPLOAD_MODULE, $imageName);
        }

        $model->deleteRecord($id);
        $this->logAction($model->getLastActionLog(), 'DELETE');
        NotificacionService::registrar('contenidos', 'DELETE', (string) (Auth::user()['id'] ?? 'ANON'), $id);

        $this->flashSuccess(FlashMessages::CONTENIDOS_DELETED);
        $this->redirect('/contenidos');
    }

    /** @return array{0: bool, 1: ?string, 2: array<string, array<int, string>>} */
    private function validateForm(array $params): array
    {
        $validator = Validator::make($params, [
            'titulo' => 'required|string|min:2|max:128',
            'subtitle' => 'nullable|string|max:300',
            'contenido' => 'required|string',
            'lugar' => 'nullable|string|max:64',
            'link' => 'nullable|string|max:255',
            'estado' => 'required|in:H,D',
        ], [
            'titulo' => 'Nombre contenido',
            'subtitle' => 'Subtitulo',
            'contenido' => 'Contenido',
            'lugar' => 'Lugar',
            'link' => 'Link',
            'estado' => 'Estado',
        ]);

        if (!$validator->passes()) {
            return [false, $validator->first(), $validator->errors()];
        }

        return [true, null, []];
    }

    /** @return array<string, string> */
    private function defaultForm(): array
    {
        return [
            'titulo' => '',
            'subtitle' => '',
            'contenido' => '',
            'imagen' => '',
            'lugar' => '',
            'link' => '',
            'estado' => 'H',
        ];
    }

    /** @return array<string, string> */
    private function mergeWithDefaults(array $data): array
    {
        return array_merge($this->defaultForm(), $data);
    }

    private function enqueueFormAssets(): void
    {
        $this->addJs('/assets/js/image-upload.js');
        $this->addCss(self::SUMMERNOTE_CSS);
        $this->addJs(self::SUMMERNOTE_JS);
        $this->addJs(self::FORM_INIT_JS);
    }

    private function renderCreateForm(array $form, ?string $error = null, array $errors = []): void
    {
        $this->renderAdminModule('contenidos/agregar', [
            'title' => UiMessages::CONTENIDOS_CREATE_TITLE,
            'user' => Auth::user(),
            'error' => $error,
            'errors' => $errors,
            'form' => $form,
        ]);
    }

    private function renderEditForm(string $id, array $form, ?string $error = null, array $errors = []): void
    {
        $this->renderAdminModule('contenidos/editar', [
            'title' => UiMessages::CONTENIDOS_EDIT_TITLE,
            'user' => Auth::user(),
            'error' => $error,
            'errors' => $errors,
            'form' => $form,
            'itemId' => $id,
        ]);
    }
}
