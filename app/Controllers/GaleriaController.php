<?php

namespace App\Controllers;

use App\Models\GaleriaModel;
use PanelAdmin\Core\Auth;
use PanelAdmin\Core\Concerns\HandlesImageUploads;
use PanelAdmin\Core\Controller;
use App\Messages\FlashMessages;
use PanelAdmin\Core\Helpers\SlugHelper;
use PanelAdmin\Core\NotificacionService;
use App\Messages\UiMessages;
use App\Messages\ValidationMessages;
use PanelAdmin\Core\Validator;

class GaleriaController extends Controller
{
    use HandlesImageUploads;

    private const UPLOAD_MODULE = 'galeria';
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const MAX_FILE_SIZE = 3145728; // 3 MB
    private const THUMB_SIZES = [
        ['w' => 150, 'h' => 150, 'mode' => 'crop'],
        ['w' => 403, 'h' => 290, 'mode' => 'crop'],
        ['w' => 1024, 'h' => 768, 'mode' => 'fit'],
    ];
    private const ESTADOS = ['H', 'D'];

    public function index(): void
    {
        $this->requireAuth();

        $list = $this->searchableIndex('/galerias', GaleriaModel::class, 'Buscar por nombre o slug...');

        $this->renderAdminModule('galeria/index', [
            'title' => UiMessages::GALERIA_INDEX_TITLE,
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

        $this->renderCreateForm([
            'nombre' => '',
            'slug' => '',
            'resumen' => '',
            'fecha' => '',
            'estado' => 'H',
        ]);
    }

    public function guardar(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $this->enqueueFormAssets();

        $model = new GaleriaModel();
        $params = $this->request->getParams();

        [$passes, $error, $errors] = $this->validateForm($params);
        if (!$passes) {
            $this->renderCreateForm($params, $error, $errors);
            return;
        }

        $slug = SlugHelper::make((string) ($params['slug'] ?? ''), (string) ($params['nombre'] ?? ''));
        if ($slug === '' || $model->existsBySlug($slug)) {
            $params['slug'] = $slug;
            $this->renderCreateForm($params, $slug === '' ? ValidationMessages::GALERIA_SLUG_INVALID : ValidationMessages::GALERIA_SLUG_ALREADY_EXISTS);
            return;
        }
        $params['slug'] = $slug;

        $upload = $this->makeUploader()->handle('portada');
        if ($upload->hasError()) {
            $this->renderCreateForm($params, $upload->getError());
            return;
        }
        $params['portada'] = $upload->isEmpty() ? '' : $this->extractImageName($upload->getPath());

        $galeriaId = $model->createRecord($params);
        $this->logAction($model->getLastActionLog(), 'CREATE');
        NotificacionService::registrar('galerias', 'CREATE', (string) (Auth::user()['id'] ?? 'ANON'), $galeriaId);

        $this->flashSuccess(FlashMessages::GALERIA_CREATED_ADD_PHOTOS);
        $this->redirect('/galerias/' . urlencode($galeriaId) . '/fotos');
    }

    public function editar(string $id): void
    {
        $this->requireAuth();
        $this->enqueueFormAssets();

        $item = (new GaleriaModel())->findById($id);
        if (!is_array($item)) {
            $this->redirect('/galerias');
            return;
        }

        $item['portada_preview'] = $this->buildImagePreviewPath((string) ($item['portada'] ?? ''));

        $this->renderEditForm($id, $item);
    }

    public function actualizar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $this->enqueueFormAssets();

        $model = new GaleriaModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/galerias');
            return;
        }

        $storedPortada = (string) ($item['portada'] ?? '');
        $params = $this->request->getParams();

        $renderError = function (string $error, array $params) use ($id, $storedPortada, $item): void {
            $params['portada_preview'] = $this->buildImagePreviewPath($storedPortada);
            $params['portada'] = $storedPortada;
            $this->renderEditForm($id, array_merge($item, $params), $error);
        };

        [$passes, $error, $errors] = $this->validateForm($params);
        if (!$passes) {
            $params['portada_preview'] = $this->buildImagePreviewPath($storedPortada);
            $params['portada'] = $storedPortada;
            $this->renderEditForm($id, array_merge($item, $params), $error, $errors);
            return;
        }

        $slug = SlugHelper::make((string) ($params['slug'] ?? ''), (string) ($params['nombre'] ?? ''));
        if ($slug === '' || $model->existsBySlug($slug, $id)) {
            $params['slug'] = $slug;
            $renderError($slug === '' ? ValidationMessages::GALERIA_SLUG_INVALID : ValidationMessages::GALERIA_SLUG_ALREADY_EXISTS, $params);
            return;
        }
        $params['slug'] = $slug;

        $portada = $storedPortada;
        $upload = $this->makeUploader()->handle('portada');
        if ($upload->hasError()) {
            $renderError($upload->getError(), $params);
            return;
        }
        if (!$upload->isEmpty()) {
            if ($storedPortada !== '') {
                $this->deleteUploadedFiles(self::UPLOAD_MODULE, $storedPortada);
            }
            $portada = $this->extractImageName($upload->getPath());
        }
        $params['portada'] = $portada;

        $model->updateRecord($id, $params);
        $this->logAction($model->getLastActionLog(), 'UPDATE');
        NotificacionService::registrar('galerias', 'UPDATE', (string) (Auth::user()['id'] ?? 'ANON'), $id);

        $this->flashSuccess(FlashMessages::GALERIA_UPDATED);
        $this->redirect('/galerias');
    }

    public function eliminar(string $id): void
    {
        $this->requireAuth();

        $model = new GaleriaModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/galerias');
            return;
        }

        $this->renderAdminModule('galeria/eliminar', [
            'title' => UiMessages::GALERIA_DELETE_TITLE,
            'user' => Auth::user(),
            'form' => $item,
            'itemId' => $id,
            'totalFotos' => $model->countFotos($id),
        ]);
    }

    public function borrar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new GaleriaModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/galerias');
            return;
        }

        foreach ($model->fotosByGaleria($id) as $foto) {
            $image = (string) ($foto['igf_image'] ?? '');
            if ($image !== '') {
                $this->deleteUploadedFiles(self::UPLOAD_MODULE, $image);
            }
        }

        $portada = (string) ($item['portada'] ?? '');
        if ($portada !== '') {
            $this->deleteUploadedFiles(self::UPLOAD_MODULE, $portada);
        }

        $model->deleteRecord($id);
        $this->logAction($model->getLastActionLog(), 'DELETE');
        NotificacionService::registrar('galerias', 'DELETE', (string) (Auth::user()['id'] ?? 'ANON'), $id);

        $this->flashSuccess(FlashMessages::GALERIA_DELETED);
        $this->redirect('/galerias');
    }

    // ------------------------------------------------------------------ Fotos

    public function fotos(string $id): void
    {
        $this->requireAuth();
        $this->enqueueFormAssets();
        $this->enqueueLightboxAssets();

        $model = new GaleriaModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/galerias');
            return;
        }

        $this->renderAdminModule('galeria/fotos', [
            'title' => UiMessages::GALERIA_FOTOS_TITLE,
            'user' => Auth::user(),
            'error' => null,
            'galeria' => $item,
            'itemId' => $id,
            'fotos' => $model->fotosByGaleria($id),
        ]);
    }

    public function fotosGuardar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $this->enqueueFormAssets();
        $this->enqueueLightboxAssets();

        $model = new GaleriaModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/galerias');
            return;
        }

        $params = $this->request->getParams();
        $alt = trim((string) ($params['igf_alt'] ?? ''));

        $renderError = function (string $error) use ($id, $item, $model): void {
            $this->renderAdminModule('galeria/fotos', [
                'title' => UiMessages::GALERIA_FOTOS_TITLE,
                'user' => Auth::user(),
                'error' => $error,
                'galeria' => $item,
                'itemId' => $id,
                'fotos' => $model->fotosByGaleria($id),
            ]);
        };

        if ($alt === '' || mb_strlen($alt) > 100) {
            $renderError(ValidationMessages::GALERIA_FOTO_ALT_REQUIRED);
            return;
        }

        $upload = $this->makeUploader()->handle('imagen');
        if ($upload->isEmpty()) {
            $renderError(ValidationMessages::GALERIA_FOTO_IMAGE_REQUIRED);
            return;
        }
        if ($upload->hasError()) {
            $renderError($upload->getError());
            return;
        }

        $fotoId = $model->createFoto($id, $this->extractImageName($upload->getPath()), $alt);
        $this->logAction($model->getLastActionLog(), 'CREATE');

        $this->flashSuccess(FlashMessages::GALERIA_FOTO_CREATED);
        $this->redirect('/galerias/' . urlencode($id) . '/fotos');
    }

    public function fotosBorrar(string $id, string $fotoId): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new GaleriaModel();
        $foto = $model->findFotoById($fotoId, $id);
        if (!is_array($foto)) {
            $this->redirect('/galerias/' . urlencode($id) . '/fotos');
            return;
        }

        $image = (string) ($foto['igf_image'] ?? '');
        if ($image !== '') {
            $this->deleteUploadedFiles(self::UPLOAD_MODULE, $image);
        }

        $model->deleteFoto($fotoId, $id);
        $this->logAction($model->getLastActionLog(), 'DELETE');

        $this->flashSuccess(FlashMessages::GALERIA_FOTO_DELETED);
        $this->redirect('/galerias/' . urlencode($id) . '/fotos');
    }

    // ------------------------------------------------------------------ Helpers

    /** @return array{0: bool, 1: ?string, 2: array<string, array<int, string>>} */
    private function validateForm(array $params): array
    {
        $validator = Validator::make($params, [
            'nombre' => 'required|string|min:2|max:100',
            'slug' => 'nullable|string|max:100',
            'resumen' => 'nullable|string',
            'fecha' => 'nullable',
            'estado' => 'required|string|min:1|max:1',
        ], [
            'nombre' => 'Nombre',
            'slug' => 'Slug',
            'resumen' => 'Resumen',
            'fecha' => 'Fecha',
            'estado' => 'Estado',
        ]);

        $passes = $validator->passes();
        $errors = $validator->errors();
        $error = $validator->first();

        if ($passes && !in_array((string) ($params['estado'] ?? ''), self::ESTADOS, true)) {
            $passes = false;
            $error = ValidationMessages::GALERIA_ESTADO_INVALIDO;
            $errors['estado'] = [$error];
        }

        return [$passes, $error, $errors];
    }

    private function enqueueFormAssets(): void
    {
        $this->addJs('/assets/js/image-upload.js');
    }

    private function enqueueLightboxAssets(): void
    {
        $this->addJs('/assets/js/admin-lightbox.js');
    }

    private function renderCreateForm(array $form, ?string $error = null, array $errors = []): void
    {
        $this->renderAdminModule('galeria/agregar', [
            'title' => UiMessages::GALERIA_CREATE_TITLE,
            'user' => Auth::user(),
            'error' => $error,
            'errors' => $errors,
            'form' => $form,
        ]);
    }

    private function renderEditForm(string $id, array $form, ?string $error = null, array $errors = []): void
    {
        $this->renderAdminModule('galeria/editar', [
            'title' => UiMessages::GALERIA_EDIT_TITLE,
            'user' => Auth::user(),
            'error' => $error,
            'errors' => $errors,
            'form' => $form,
            'itemId' => $id,
        ]);
    }
}
