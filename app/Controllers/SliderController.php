<?php

namespace App\Controllers;

use App\Models\SliderModel;
use PanelAdmin\Core\Auth;
use PanelAdmin\Core\Concerns\HandlesImageUploads;
use PanelAdmin\Core\Controller;
use App\Messages\FlashMessages;
use PanelAdmin\Core\NotificacionService;
use App\Messages\UiMessages;
use App\Messages\ValidationMessages;
use PanelAdmin\Core\Validator;
use Throwable;

class SliderController extends Controller
{
    use HandlesImageUploads;

    private const UPLOAD_MODULE = 'slider';
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const MAX_FILE_SIZE = 2097152; // 2 MB
    private const THUMB_SIZES = [
        ['w' => 150, 'h' => 150, 'mode' => 'crop'], // slider: solo miniatura para admin/listado
    ];
    private const RANGE_SLIDER_CSS = '/assets/theme-one/css/range-slider.css';
    private const RANGE_SLIDER_JS = '/assets/theme-one/js/range-slider/ion.rangeSlider.min.js';
    private const OVERLAY_INIT_JS = '/assets/js/slider-overlay-range.js';

    public function index(): void
    {
        $this->requireAuth();
        $this->addJs('/assets/js/admin-reorder.js');

        $user = Auth::user();
        $items = [];
        $page = $this->getCurrentPage();
        $perPage = $this->getDefaultPerPage();
        $filters = $this->getQueryParams(['q' => '']);
        $search = (string) ($filters['q'] ?? '');
        $pagination = $this->buildPagination(0, $page, $perPage, '/slider', ['q' => $search]);

        try {
            $model = new SliderModel();
            $totalRows = $model->countAll($search);
            $pagination = $this->buildPagination($totalRows, $page, $perPage, '/slider', ['q' => $search]);
            $items = $model->paginate((int) $pagination['offset'], (int) $pagination['perPage'], $search);
        } catch (Throwable) {
            $items = [];
        }

        $this->renderAdminModule('slider/index', [
            'title' => UiMessages::SLIDER_INDEX_TITLE,
            'user' => $user,
            'items' => $items,
            'pagination' => $pagination,
            'search' => $search,
            'searchConfig' => [
                'action' => '/slider',
                'method' => 'GET',
                'fields' => [[
                    'name' => 'q',
                    'type' => 'text',
                    'placeholder' => 'Buscar por titulo o subtitulo...',
                    'value' => $search,
                    'icon' => 'fa fa-search',
                ]],
                'submitLabel' => 'Buscar',
                'submitIcon' => 'fa fa-search',
                'clearUrl' => $search !== '' ? '/slider' : '',
            ],
        ]);
    }

    public function agregar(): void
    {
        $this->requireAuth();
        $this->enqueueFormAssets();

        $model = new SliderModel();
        $form = $this->defaultForm();
        $form['orden'] = (string) $model->getSuggestedOrder();

        $this->renderCreateForm($form);
    }

    public function guardar(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $this->enqueueFormAssets();

        $model = new SliderModel();
        $params = $this->request->getParams();

        if (trim((string) ($params['estado'] ?? '')) === '') {
            $params['estado'] = 'H';
        }

        [$passes, $error, $errors] = $this->validateForm($params);
        $titulo = trim((string) ($params['titulo'] ?? ''));

        if (!$passes) {
            $this->renderCreateForm($this->mergeWithDefaults($params), $error, $errors);
            return;
        }

        if ($model->existsByName($titulo)) {
            $this->renderCreateForm($this->mergeWithDefaults($params), ValidationMessages::SLIDER_ALREADY_EXISTS);
            return;
        }

        $upload = $this->makeUploader()->handle('imagen');
        if ($upload->hasError()) {
            $this->renderCreateForm($this->mergeWithDefaults($params), $upload->getError());
            return;
        }

        $params['imagen'] = $this->extractImageName($upload->getPath());
        $createdId = $model->createRecord($params);
        $this->logAction($model->getLastActionLog(), 'CREATE');
        NotificacionService::registrar('slider', 'CREATE', (string) (Auth::user()['id'] ?? 'ANON'), $createdId);
        $this->flashSuccess(FlashMessages::SLIDER_CREATED);
        $this->redirect('/slider');
    }

    public function editar(string $id): void
    {
        $this->requireAuth();
        $this->enqueueFormAssets();

        $item = (new SliderModel())->findById($id);
        if (!is_array($item)) {
            $this->redirect('/slider');
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

        $model = new SliderModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/slider');
            return;
        }

        $storedImageName = $this->extractImageName((string) ($item['imagen'] ?? ''));
        $previewImagePath = $this->buildImagePreviewPath($storedImageName);

        $params = $this->request->getParams();
        [$passes, $error, $errors] = $this->validateForm($params);
        $titulo = trim((string) ($params['titulo'] ?? ''));

        if (!$passes) {
            $params['imagen'] = $previewImagePath;

            $this->renderEditForm($id, $this->mergeWithDefaults($params), $error, $errors);
            return;
        }

        if ($model->existsByName($titulo, $id)) {
            $params['imagen'] = $previewImagePath;

            $this->renderEditForm($id, $this->mergeWithDefaults($params), ValidationMessages::SLIDER_ALREADY_EXISTS);
            return;
        }

        $imagenPath = $storedImageName;
        $upload = $this->makeUploader()->handle('imagen');

        if ($upload->hasError()) {
            $params['imagen'] = $previewImagePath;

            $this->renderEditForm($id, $this->mergeWithDefaults($params), $upload->getError());
            return;
        }

        if (!$upload->isEmpty()) {
            $this->deleteUploadedFiles(self::UPLOAD_MODULE, $storedImageName);
            $imagenPath = $this->extractImageName($upload->getPath());
        }

        $params['imagen'] = $imagenPath;
        $model->updateRecord($id, $params);
        $this->logAction($model->getLastActionLog(), 'UPDATE');
        NotificacionService::registrar('slider', 'UPDATE', (string) (Auth::user()['id'] ?? 'ANON'), $id);
        $this->flashSuccess(FlashMessages::SLIDER_UPDATED);
        $this->redirect('/slider');
    }

    public function eliminar(string $id): void
    {
        $this->requireAuth();

        $model = new SliderModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/slider');
            return;
        }

        $this->renderAdminModule('slider/eliminar', [
            'title' => UiMessages::SLIDER_DELETE_TITLE,
            'user' => Auth::user(),
            'form' => $item,
            'itemId' => $id,
        ]);
    }

    public function borrar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new SliderModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/slider');
            return;
        }

        $storedImage = $this->extractImageName((string) ($item['imagen'] ?? ''));
        $model->deleteRecord($id);
        $this->logAction($model->getLastActionLog(), 'DELETE');
        NotificacionService::registrar('slider', 'DELETE', (string) (Auth::user()['id'] ?? 'ANON'), $id);
        $this->deleteUploadedFiles(self::UPLOAD_MODULE, $storedImage);
        $this->flashSuccess(FlashMessages::SLIDER_DELETED);
        $this->redirect('/slider');
    }

    /** @return array{0: bool, 1: ?string, 2: array<string, array<int, string>>} */
    private function validateForm(array $params): array
    {
        $validator = Validator::make($params, [
            'titulo' => 'required|string|min:2|max:128',
            'subtitle' => 'required|string|min:2|max:200',
            'description' => 'nullable|string',
            'overlay' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
            'orden' => 'nullable|regex:/^\d+$/',
            'estado' => 'required|in:H,D',
            'link_btn' => 'nullable|string|max:128',
            'nombre_btn' => 'nullable|string|max:128',
        ], [
            'titulo' => 'Titulo',
            'subtitle' => 'Subtitulo',
            'description' => 'Descripcion',
            'overlay' => 'Overlay',
            'orden' => 'Orden',
            'estado' => 'Estado',
            'link_btn' => 'Link boton',
            'nombre_btn' => 'Nombre boton',
        ]);

        $passes = $validator->passes();
        if (!$passes) {
            return [false, $validator->first(), $validator->errors()];
        }

        $overlayRaw = trim((string) ($params['overlay'] ?? ''));
        if ($overlayRaw !== '') {
            $overlay = (float) $overlayRaw;
            if ($overlay < 0 || $overlay > 0.99) {
                return [false, ValidationMessages::SLIDER_OVERLAY_INVALIDO, []];
            }
        }

        $ordenRaw = trim((string) ($params['orden'] ?? ''));
        if ($ordenRaw !== '') {
            $orden = (int) $ordenRaw;
            if ($orden < 1 || $orden > 255) {
                return [false, 'El orden debe estar entre 1 y 255.', []];
            }
        }

        $estado = trim((string) ($params['estado'] ?? ''));
        if (!in_array($estado, ['H', 'D'], true)) {
            return [false, ValidationMessages::SLIDER_ESTADO_INVALIDO, []];
        }

        return [true, null, []];
    }

    /** @return array<string, mixed> */
    private function defaultForm(): array
    {
        return [
            'titulo' => '',
            'subtitle' => '',
            'description' => '',
            'imagen' => '',
            'overlay' => '',
            'orden' => '',
            'estado' => 'H',
            'link_btn' => '',
            'nombre_btn' => '',
        ];
    }

    public function ordenar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $direction = strtolower(trim((string) ($this->request->getParams()['direction'] ?? '')));
        if (!in_array($direction, ['up', 'down'], true)) {
            $this->jsonResponse(['ok' => false, 'error' => 'Direccion invalida.'], 400);
            return;
        }

        $model = new SliderModel();
        if (!is_array($model->findById($id))) {
            $this->jsonResponse(['ok' => false, 'error' => 'Registro no encontrado.'], 404);
            return;
        }

        try {
            $orden = $model->moveRecord($id, $direction);
        } catch (Throwable $e) {
            error_log('SliderController::ordenar error: ' . $e->getMessage());
            $this->jsonResponse(['ok' => false, 'error' => 'No se pudo actualizar el orden.'], 500);
            return;
        }

        if ($orden === null) {
            $this->jsonResponse(['ok' => true, 'moved' => false]);
            return;
        }

        $this->logAction("Slider reordenado (ID {$id}, {$direction})", 'UPDATE');

        $this->jsonResponse(['ok' => true, 'moved' => true, 'orden' => $orden]);
    }

    /** @return array<string, mixed> */
    private function mergeWithDefaults(array $data): array
    {
        return array_merge($this->defaultForm(), $data);
    }

    private function enqueueFormAssets(): void
    {
        $this->addCss(self::RANGE_SLIDER_CSS);
        $this->addJs(self::RANGE_SLIDER_JS);
        $this->addJs(self::OVERLAY_INIT_JS);
        $this->addJs('/assets/js/image-upload.js');
    }

    private function renderCreateForm(array $form, ?string $error = null, array $errors = []): void
    {
        $this->renderAdminModule('slider/agregar', [
            'title' => UiMessages::SLIDER_CREATE_TITLE,
            'user' => Auth::user(),
            'error' => $error,
            'errors' => $errors,
            'form' => $form,
        ]);
    }

    private function renderEditForm(string $id, array $form, ?string $error = null, array $errors = []): void
    {
        $this->renderAdminModule('slider/editar', [
            'title' => UiMessages::SLIDER_EDIT_TITLE,
            'user' => Auth::user(),
            'error' => $error,
            'errors' => $errors,
            'form' => $form,
            'itemId' => $id,
        ]);
    }
}
