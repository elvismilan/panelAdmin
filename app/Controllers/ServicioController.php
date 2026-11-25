<?php

namespace App\Controllers;

use App\Models\ServicioModel;
use PanelAdmin\Core\Auth;
use PanelAdmin\Core\Concerns\HandlesImageUploads;
use PanelAdmin\Core\Controller;
use App\Messages\FlashMessages;
use PanelAdmin\Core\Helpers\SlugHelper;
use PanelAdmin\Core\ImageUploader;
use PanelAdmin\Core\NotificacionService;
use App\Messages\UiMessages;
use App\Messages\ValidationMessages;
use PanelAdmin\Core\Validator;

class ServicioController extends Controller
{
    use HandlesImageUploads;

    private const UPLOAD_MODULE = 'servicios';
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const MAX_FILE_SIZE = 3145728; // 3 MB
    private const MAX_ICON_SIZE = 1048576; // 1 MB
    private const THUMB_SIZES = [
        ['w' => 150, 'h' => 150, 'mode' => 'crop'],
        ['w' => 641, 'h' => 430, 'mode' => 'crop'],
        ['w' => 1600, 'h' => 900, 'mode' => 'fit'],
    ];
    private const ESTADOS = ['H', 'D'];
    private const SUMMERNOTE_CSS = '/assets/theme-one/css/summernote.css';
    private const SUMMERNOTE_JS = '/assets/theme-one/js/editor/summernote/summernote.js';

    public function index(): void
    {
        $this->requireAuth();

        $list = $this->searchableIndex('/servicios', ServicioModel::class, 'Buscar por titulo o slug...');

        $this->renderAdminModule('servicio/index', [
            'title' => UiMessages::SERVICIO_INDEX_TITLE,
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

        $this->renderAdminModule('servicio/agregar', [
            'title' => UiMessages::SERVICIO_CREATE_TITLE,
            'user' => Auth::user(),
            'error' => null,
            'form' => [
                'titulo' => '',
                'subtitulo' => '',
                'slug' => '',
                'contenido' => '',
                'icon' => '',
                'estado' => 'H',
            ],
        ]);
    }

    public function guardar(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $this->enqueueFormAssets();

        $model = new ServicioModel();
        $params = $this->request->getParams();

        $renderError = function (?string $error, array $params, array $errors = []): void {
            $this->renderAdminModule('servicio/agregar', [
                'title' => UiMessages::SERVICIO_CREATE_TITLE,
                'user' => Auth::user(),
                'error' => $error,
                'errors' => $errors,
                'form' => $params,
            ]);
        };

        [$passes, $error, $errors] = $this->validateForm($params);
        if (!$passes) {
            $renderError($error, $params, $errors);
            return;
        }

        $slug = SlugHelper::make((string) ($params['slug'] ?? ''), (string) ($params['titulo'] ?? ''));
        if ($slug === '' || $model->existsBySlug($slug)) {
            $params['slug'] = $slug;
            $renderError($slug === '' ? ValidationMessages::SERVICIO_SLUG_INVALID : ValidationMessages::SERVICIO_SLUG_ALREADY_EXISTS, $params);
            return;
        }
        $params['slug'] = $slug;

        $upload = $this->makeUploader()->handle('imagen');
        if ($upload->hasError()) {
            $renderError($upload->getError(), $params);
            return;
        }
        $params['imagen'] = $upload->isEmpty() ? '' : $this->extractImageName($upload->getPath());

        $iconUpload = $this->makeIconUploader()->handle('icono');
        if ($iconUpload->hasError()) {
            $renderError($iconUpload->getError(), $params);
            return;
        }
        $params['icon'] = $iconUpload->isEmpty() ? '' : $this->extractImageName($iconUpload->getPath());

        $servicioId = $model->createRecord($params);
        $this->logAction($model->getLastActionLog(), 'CREATE');
        NotificacionService::registrar('servicios', 'CREATE', (string) (Auth::user()['id'] ?? 'ANON'), $servicioId);

        $this->flashSuccess(FlashMessages::SERVICIO_CREATED);
        $this->redirect('/servicios');
    }

    public function editar(string $id): void
    {
        $this->requireAuth();
        $this->enqueueFormAssets();

        $item = (new ServicioModel())->findById($id);
        if (!is_array($item)) {
            $this->redirect('/servicios');
            return;
        }

        $item['imagen_preview'] = $this->buildImagePreviewPath((string) ($item['imagen'] ?? ''));
        $item['icon_preview'] = $this->buildImagePreviewPath((string) ($item['icon'] ?? ''));

        $this->renderAdminModule('servicio/editar', [
            'title' => UiMessages::SERVICIO_EDIT_TITLE,
            'user' => Auth::user(),
            'error' => null,
            'form' => $item,
            'itemId' => $id,
        ]);
    }

    public function actualizar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $this->enqueueFormAssets();

        $model = new ServicioModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/servicios');
            return;
        }

        $storedImage = (string) ($item['imagen'] ?? '');
        $storedIcon = (string) ($item['icon'] ?? '');
        $params = $this->request->getParams();

        $renderError = function (?string $error, array $params, array $errors = []) use ($id, $item, $storedImage, $storedIcon): void {
            $params['imagen_preview'] = $this->buildImagePreviewPath($storedImage);
            $params['imagen'] = $storedImage;
            $params['icon_preview'] = $this->buildImagePreviewPath($storedIcon);
            $params['icon'] = $storedIcon;
            $this->renderAdminModule('servicio/editar', [
                'title' => UiMessages::SERVICIO_EDIT_TITLE,
                'user' => Auth::user(),
                'error' => $error,
                'errors' => $errors,
                'form' => array_merge($item, $params),
                'itemId' => $id,
            ]);
        };

        [$passes, $error, $errors] = $this->validateForm($params);
        if (!$passes) {
            $renderError($error, $params, $errors);
            return;
        }

        // El slug no cambia al editar (el formulario no lo muestra y antes se regeneraba
        // desde el titulo): cambiar la URL de un servicio publicado pierde su posicionamiento.
        $storedSlug = trim((string) ($item['slug'] ?? ''));
        $slug = $storedSlug !== '' ? $storedSlug : SlugHelper::make((string) ($params['slug'] ?? ''), (string) ($params['titulo'] ?? ''));
        if ($slug === '' || $model->existsBySlug($slug, $id)) {
            $params['slug'] = $slug;
            $renderError($slug === '' ? ValidationMessages::SERVICIO_SLUG_INVALID : ValidationMessages::SERVICIO_SLUG_ALREADY_EXISTS, $params);
            return;
        }
        $params['slug'] = $slug;

        $imagen = $storedImage;
        $upload = $this->makeUploader()->handle('imagen');
        if ($upload->hasError()) {
            $renderError($upload->getError(), $params);
            return;
        }
        if (!$upload->isEmpty()) {
            if ($storedImage !== '') {
                $this->deleteUploadedFiles(self::UPLOAD_MODULE, $storedImage);
            }
            $imagen = $this->extractImageName($upload->getPath());
        }
        $params['imagen'] = $imagen;

        $icon = $storedIcon;
        $iconUpload = $this->makeIconUploader()->handle('icono');
        if ($iconUpload->hasError()) {
            $renderError($iconUpload->getError(), $params);
            return;
        }
        if (!$iconUpload->isEmpty()) {
            if ($storedIcon !== '') {
                $this->deleteUploadedFiles(self::UPLOAD_MODULE, $storedIcon);
            }
            $icon = $this->extractImageName($iconUpload->getPath());
        }
        $params['icon'] = $icon;

        $model->updateRecord($id, $params);
        $this->logAction($model->getLastActionLog(), 'UPDATE');
        NotificacionService::registrar('servicios', 'UPDATE', (string) (Auth::user()['id'] ?? 'ANON'), $id);

        $this->flashSuccess(FlashMessages::SERVICIO_UPDATED);
        $this->redirect('/servicios');
    }

    public function eliminar(string $id): void
    {
        $this->requireAuth();

        $model = new ServicioModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/servicios');
            return;
        }

        $this->renderAdminModule('servicio/eliminar', [
            'title' => UiMessages::SERVICIO_DELETE_TITLE,
            'user' => Auth::user(),
            'form' => $item,
            'itemId' => $id,
        ]);
    }

    public function borrar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new ServicioModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/servicios');
            return;
        }

        $imagen = (string) ($item['imagen'] ?? '');
        if ($imagen !== '') {
            $this->deleteUploadedFiles(self::UPLOAD_MODULE, $imagen);
        }

        $icon = (string) ($item['icon'] ?? '');
        if ($icon !== '') {
            $this->deleteUploadedFiles(self::UPLOAD_MODULE, $icon);
        }

        $model->deleteRecord($id);
        $this->logAction($model->getLastActionLog(), 'DELETE');
        NotificacionService::registrar('servicios', 'DELETE', (string) (Auth::user()['id'] ?? 'ANON'), $id);

        $this->flashSuccess(FlashMessages::SERVICIO_DELETED);
        $this->redirect('/servicios');
    }

    // ------------------------------------------------------------------ Helpers

    /** @return array{0: bool, 1: ?string, 2: array<string, array<int, string>>} */
    private function validateForm(array $params): array
    {
        $validator = Validator::make($params, [
            'titulo' => 'required|string|min:2|max:255',
            'subtitulo' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'contenido' => 'nullable|string',
            'estado' => 'required|string|min:1|max:1',
        ], [
            'titulo' => 'Titulo',
            'subtitulo' => 'Subtitulo',
            'slug' => 'Slug',
            'contenido' => 'Contenido',
            'estado' => 'Estado',
        ]);

        $passes = $validator->passes();
        $errors = $validator->errors();
        $error = $validator->first();

        if ($passes && !in_array((string) ($params['estado'] ?? ''), self::ESTADOS, true)) {
            $passes = false;
            $error = ValidationMessages::SERVICIO_ESTADO_INVALIDO;
            $errors['estado'] = [$error];
        }

        return [$passes, $error, $errors];
    }

    private function enqueueFormAssets(): void
    {
        $this->addJs('/assets/js/image-upload.js');
        $this->addCss(self::SUMMERNOTE_CSS);
        $this->addJs(self::SUMMERNOTE_JS);
        $this->addJs('/assets/js/contenidos-form.js');
    }

    private function makeIconUploader(): ImageUploader
    {
        return new ImageUploader([
            'module' => self::UPLOAD_MODULE,
            'allowedTypes' => self::ALLOWED_TYPES,
            'maxSize' => self::MAX_ICON_SIZE,
            'thumbs' => [],
        ]);
    }
}
