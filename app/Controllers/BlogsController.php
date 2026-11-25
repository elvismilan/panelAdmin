<?php

namespace App\Controllers;

use App\Models\BlogsModel;
use App\Models\CategoriaModel;
use App\Models\AutorModel;
use PanelAdmin\Core\Auth;
use PanelAdmin\Core\Concerns\HandlesImageUploads;
use PanelAdmin\Core\Controller;
use App\Messages\FlashMessages;
use PanelAdmin\Core\Helpers\SlugHelper;
use PanelAdmin\Core\NotificacionService;
use App\Messages\UiMessages;
use App\Messages\ValidationMessages;
use PanelAdmin\Core\Validator;
use Throwable;

class BlogsController extends Controller
{
    use HandlesImageUploads;

    private const BLOG_CATEGORY_TYPE = 'blog';
    private const UPLOAD_MODULE = 'blogs';
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const MAX_FILE_SIZE = 3145728; // 3 MB
    private const THUMB_SIZES = [
        ['w' => 150, 'h' => 150, 'mode' => 'crop'],
        ['w' => 641, 'h' => 430, 'mode' => 'crop'],
        ['w' => 403, 'h' => 290, 'mode' => 'crop'],
        ['w' => 806, 'h' => 483, 'mode' => 'crop'],
        ['w' => 1600, 'h' => 900, 'mode' => 'crop'],
    ];
    private const SUMMERNOTE_CSS = '/assets/theme-one/css/summernote.css';
    private const SUMMERNOTE_JS = '/assets/theme-one/js/editor/summernote/summernote.js';
    private const SELECT2_CSS = '/assets/theme-one/css/select2.css';
    private const SELECT2_JS = '/assets/theme-one/js/select2/select2.full.min.js';
    private const FORM_INIT_JS = '/assets/js/blogs-form.js';

    public function index(): void
    {
        $this->requireAuth();

        $list = $this->searchableIndex('/blogs', BlogsModel::class, 'Buscar por titulo, slug, autor o resumen...');

        try {
            $blogCategories = (new CategoriaModel())->getCategoriesByType(self::BLOG_CATEGORY_TYPE, false);
        } catch (Throwable) {
            $blogCategories = [];
        }

        $this->renderAdminModule('blogs/index', [
            'title' => UiMessages::BLOGS_INDEX_TITLE,
            'user' => Auth::user(),
            'items' => $list['items'],
            'blogCategories' => $blogCategories,
            'pagination' => $list['pagination'],
            'search' => $list['search'],
            'searchConfig' => $list['searchConfig'],
        ]);
    }

    public function agregar(): void
    {
        $this->requireAuth();
        $this->enqueueFormAssets();
        $user = Auth::user();
        $form = $this->defaultForm();
        $form['autor'] = $this->currentAuthorName($user);

        $this->renderCreateForm($form);
    }

    public function guardar(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $this->enqueueFormAssets();

        $model = new BlogsModel();
        $user = Auth::user();
        $params = $this->request->getParams();
        $params['autor'] = trim((string) ($params['autor'] ?? '')) !== '' ? trim((string) $params['autor']) : $this->currentAuthorName($user);
        $params['post_category_ids'] = CategoriaModel::normalizeIds($params['post_category_ids'] ?? []);

        if (trim((string) ($params['estado'] ?? '')) === '') {
            $params['estado'] = 'H';
        }

        [$passes, $error, $errors] = $this->validateForm($params);
        if (!$passes) {
            $this->renderCreateForm($this->mergeWithDefaults($params), $error, $errors);
            return;
        }

        $slug = SlugHelper::make((string) ($params['slug'] ?? ''), (string) ($params['titulo'] ?? ''));
        if ($slug === '') {
            $this->renderCreateForm($this->mergeWithDefaults($params), ValidationMessages::BLOGS_SLUG_INVALID);
            return;
        }

        if ($model->existsBySlug($slug)) {
            $params['slug'] = $slug;
            $this->renderCreateForm($this->mergeWithDefaults($params), ValidationMessages::BLOGS_SLUG_ALREADY_EXISTS);
            return;
        }

        $upload = $this->makeUploader()->handle('imagen');
        if ($upload->hasError()) {
            $this->renderCreateForm($this->mergeWithDefaults($params), $upload->getError());
            return;
        }

        $params['slug'] = $slug;
        $params['imagen'] = $this->extractImageName($upload->getPath());
        $params['post_category_ids'] = (new CategoriaModel())->filterValidIds($params['post_category_ids'], self::BLOG_CATEGORY_TYPE, true);

        $createdId = $model->createRecord($params);
        $this->logAction($model->getLastActionLog(), 'CREATE');
        NotificacionService::registrar('blogs', 'CREATE', (string) (Auth::user()['id'] ?? 'ANON'), $createdId);

        $this->flashSuccess(FlashMessages::BLOGS_CREATED);
        $this->redirect('/blogs');
    }

    public function editar(string $id): void
    {
        $this->requireAuth();
        $this->enqueueFormAssets();

        $item = (new BlogsModel())->findById($id);
        if (!is_array($item)) {
            $this->redirect('/blogs');
            return;
        }

        $user = Auth::user();
        $item['imagen'] = $this->buildImagePreviewPath((string) ($item['imagen'] ?? ''));
        $item['autor'] = trim((string) ($item['autor'] ?? '')) !== '' ? trim((string) $item['autor']) : $this->currentAuthorName($user);
        $item['post_category_ids'] = (new CategoriaModel())->getIdsByRelation(self::BLOG_CATEGORY_TYPE, (int) $id);

        $this->renderEditForm($id, $this->mergeWithDefaults($item));
    }

    public function actualizar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $this->enqueueFormAssets();

        $model = new BlogsModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/blogs');
            return;
        }

        $storedImageName = $this->extractImageName((string) ($item['imagen'] ?? ''));
        $previewImagePath = $this->buildImagePreviewPath($storedImageName);

        $user = Auth::user();
        $params = $this->request->getParams();
        $storedAuthor = trim((string) ($item['autor'] ?? ''));
        $params['autor'] = trim((string) ($params['autor'] ?? '')) !== ''
            ? trim((string) $params['autor'])
            : ($storedAuthor !== '' ? $storedAuthor : $this->currentAuthorName($user));
        $params['post_category_ids'] = CategoriaModel::normalizeIds($params['post_category_ids'] ?? []);

        [$passes, $error, $errors] = $this->validateForm($params, $storedAuthor);
        if (!$passes) {
            $params['imagen'] = $previewImagePath;
            $this->renderEditForm($id, $this->mergeWithDefaults($params), $error, $errors);
            return;
        }

        // El slug no cambia al editar: cambiar la URL de un articulo publicado pierde su posicionamiento.
        $storedSlug = trim((string) ($item['slug'] ?? ''));
        $slug = $storedSlug !== '' ? $storedSlug : SlugHelper::make((string) ($params['slug'] ?? ''), (string) ($params['titulo'] ?? ''));
        if ($slug === '') {
            $params['imagen'] = $previewImagePath;
            $this->renderEditForm($id, $this->mergeWithDefaults($params), ValidationMessages::BLOGS_SLUG_INVALID);
            return;
        }

        if ($model->existsBySlug($slug, $id)) {
            $params['slug'] = $slug;
            $params['imagen'] = $previewImagePath;
            $this->renderEditForm($id, $this->mergeWithDefaults($params), ValidationMessages::BLOGS_SLUG_ALREADY_EXISTS);
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
            $this->deleteUploadedFiles(self::UPLOAD_MODULE, $storedImageName);
            $imageName = $this->extractImageName($upload->getPath());
        }

        $params['slug'] = $slug;
        $params['imagen'] = $imageName;
        $params['post_category_ids'] = (new CategoriaModel())->filterValidIds($params['post_category_ids'], self::BLOG_CATEGORY_TYPE, true);
        $model->updateRecord($id, $params);
        $this->logAction($model->getLastActionLog(), 'UPDATE');
        NotificacionService::registrar('blogs', 'UPDATE', (string) (Auth::user()['id'] ?? 'ANON'), $id);

        $this->flashSuccess(FlashMessages::BLOGS_UPDATED);
        $this->redirect('/blogs');
    }

    public function eliminar(string $id): void
    {
        $this->requireAuth();

        $model = new BlogsModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/blogs');
            return;
        }

        $this->renderAdminModule('blogs/eliminar', [
            'title' => UiMessages::BLOGS_DELETE_TITLE,
            'user' => Auth::user(),
            'form' => $item,
            'itemId' => $id,
        ]);
    }

    public function borrar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new BlogsModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/blogs');
            return;
        }

        $storedImage = $this->extractImageName((string) ($item['imagen'] ?? ''));
        $model->deleteRecord($id);
        $this->logAction($model->getLastActionLog(), 'DELETE');
        NotificacionService::registrar('blogs', 'DELETE', (string) (Auth::user()['id'] ?? 'ANON'), $id);
        $this->deleteUploadedFiles(self::UPLOAD_MODULE, $storedImage);

        $this->flashSuccess(FlashMessages::BLOGS_DELETED);
        $this->redirect('/blogs');
    }

    public function guardarCategoria(): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $categorias = new CategoriaModel();
        $params = $this->request->getParams();
        $name = trim((string) ($params['cat_name'] ?? ''));

        $nameLength = function_exists('mb_strlen') ? mb_strlen($name) : strlen($name);
        if ($name === '' || $nameLength < 2 || $nameLength > 150) {
            $this->flashError('La categoria debe tener entre 2 y 150 caracteres.');
            $this->redirect('/blogs');
            return;
        }

        $slug = SlugHelper::make((string) ($params['cat_slug'] ?? ''), $name);
        if ($slug === '') {
            $this->flashError('No se pudo generar un slug valido para la categoria.');
            $this->redirect('/blogs');
            return;
        }

        if ($categorias->existsNameByType($name, self::BLOG_CATEGORY_TYPE) || $categorias->existsSlugByType($slug, self::BLOG_CATEGORY_TYPE)) {
            $this->flash('warning', 'La categoria ya existe para el modulo Blog.');
            $this->redirect('/blogs');
            return;
        }

        $categoryId = $categorias->createCategory($name, $slug, self::BLOG_CATEGORY_TYPE);
        $this->logAction('BLOG_CATEGORY_CREATE|' . $categoryId . '|' . $name, 'CREATE');
        $this->flashSuccess('Categoria creada correctamente.');
        $this->redirect('/blogs');
    }

    public function borrarCategoria(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $categoryId = (int) $id;
        if ($categoryId <= 0) {
            $this->flashError('Categoria invalida.');
            $this->redirect('/blogs');
            return;
        }

        $categorias = new CategoriaModel();
        $category = $categorias->findByIdForType($categoryId, self::BLOG_CATEGORY_TYPE);
        if (!is_array($category)) {
            $this->flash('warning', 'La categoria no existe o no pertenece a Blog.');
            $this->redirect('/blogs');
            return;
        }

        $usage = $categorias->countRelations($categoryId, self::BLOG_CATEGORY_TYPE);
        if ($usage > 0) {
            $this->flash('warning', 'No se puede eliminar la categoria porque ya esta asociada a uno o mas posts.');
            $this->redirect('/blogs');
            return;
        }

        $categorias->deleteCategory($categoryId);
        $this->logAction('BLOG_CATEGORY_DELETE|' . $categoryId . '|' . (string) ($category['cat_name'] ?? ''), 'DELETE');
        $this->flashSuccess('Categoria eliminada correctamente.');
        $this->redirect('/blogs');
    }

    /** @return array{0: bool, 1: ?string, 2: array<string, array<int, string>>} */
    private function validateForm(array $params, string $storedAuthor = ''): array
    {
        $validator = Validator::make($params, [
            'titulo' => 'required|string|min:2|max:255',
            'slug' => 'nullable|string|min:2|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'detalle' => 'nullable|string|max:160',
            'contenido' => 'required|string',
            'autor' => 'nullable|string|max:32',
            'estado' => 'required|in:H,D',
        ], [
            'titulo' => 'Titulo',
            'slug' => 'Slug',
            'detalle' => 'Resumen',
            'contenido' => 'Contenido',
            'autor' => 'Autor',
            'estado' => 'Estado',
        ]);

        if (!$validator->passes()) {
            return [false, $validator->first(), $validator->errors()];
        }

        $selectedCategoryIds = CategoriaModel::normalizeIds($params['post_category_ids'] ?? []);
        if ($selectedCategoryIds !== []) {
            $validIds = (new CategoriaModel())->filterValidIds($selectedCategoryIds, self::BLOG_CATEGORY_TYPE, true);
            if (count($validIds) !== count($selectedCategoryIds)) {
                return [false, 'Una o mas categorias seleccionadas no son validas para Blog.', ['post_category_ids' => ['Una o mas categorias seleccionadas no son validas para Blog.']]];
            }
        }

        $allowedAuthors = array_column($this->authorOptions($storedAuthor), 'value');
        if (!in_array(trim((string) ($params['autor'] ?? '')), $allowedAuthors, true)) {
            return [false, 'Selecciona un autor valido de la lista.', ['autor' => ['Selecciona un autor valido de la lista.']]];
        }

        return [true, null, []];
    }

    /** @return array<string, mixed> */
    private function defaultForm(): array
    {
        return [
            'titulo' => '',
            'slug' => '',
            'detalle' => '',
            'contenido' => '',
            'imagen' => '',
            'autor' => '',
            'post_category_ids' => [],
            'estado' => 'H',
        ];
    }

    /** @return array<string, mixed> */
    private function mergeWithDefaults(array $data): array
    {
        return array_merge($this->defaultForm(), $data);
    }

    private function enqueueFormAssets(): void
    {
        $this->addJs('/assets/js/image-upload.js');
        $this->addCss(self::SELECT2_CSS);
        $this->addJs(self::SELECT2_JS);
        $this->addCss(self::SUMMERNOTE_CSS);
        $this->addJs(self::SUMMERNOTE_JS);
        $this->addJs(self::FORM_INIT_JS);
    }

    private function currentAuthorName(?array $user): string
    {
        $candidate = trim((string) (($user['full_name'] ?? '') !== '' ? $user['full_name'] : ($user['username'] ?? '')));
        if ($candidate === '') {
            $candidate = 'ANON';
        }

        return $this->limitAuthorName($candidate);
    }

    /** posts.autor es varchar(32). */
    private function limitAuthorName(string $name): string
    {
        $name = trim($name);

        return function_exists('mb_substr') ? (string) mb_substr($name, 0, 32) : substr($name, 0, 32);
    }

    /**
     * Autores elegibles: usuarios activos, con el mismo nombre que arma el login.
     * Si el post ya tiene un autor que no es un usuario activo, se agrega como "(actual)"
     * para no perderlo al guardar.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function authorOptions(string $storedAuthor = ''): array
    {
        $names = [];
        try {
            foreach ((new AutorModel())->getActiveForSelect() as $row) {
                $nombre = trim((string) ($row['nombre'] ?? ''));
                $name = $this->limitAuthorName($nombre !== '' ? $nombre : (string) ($row['usu_id'] ?? ''));
                if ($name !== '' && !in_array($name, $names, true)) {
                    $names[] = $name;
                }
            }
        } catch (Throwable $e) {
            error_log('BlogsController authorOptions error: ' . $e->getMessage());
        }

        $options = array_map(static fn (string $name): array => ['value' => $name, 'label' => $name], $names);
        $storedAuthor = trim($storedAuthor);
        if ($storedAuthor !== '' && !in_array($storedAuthor, $names, true)) {
            array_unshift($options, ['value' => $storedAuthor, 'label' => $storedAuthor . ' (actual)']);
        }

        return $options;
    }

    private function renderCreateForm(array $form, ?string $error = null, array $errors = []): void
    {
        $this->renderAdminModule('blogs/agregar', [
            'title' => UiMessages::BLOGS_CREATE_TITLE,
            'user' => Auth::user(),
            'error' => $error,
            'errors' => $errors,
            'categoryOptions' => (new CategoriaModel())->getActiveByType(self::BLOG_CATEGORY_TYPE),
            'authorOptions' => $this->authorOptions(),
            'form' => $form,
        ]);
    }

    private function renderEditForm(string $id, array $form, ?string $error = null, array $errors = []): void
    {
        $this->renderAdminModule('blogs/editar', [
            'title' => UiMessages::BLOGS_EDIT_TITLE,
            'user' => Auth::user(),
            'error' => $error,
            'errors' => $errors,
            'categoryOptions' => (new CategoriaModel())->getActiveByType(self::BLOG_CATEGORY_TYPE),
            'authorOptions' => $this->authorOptions((string) ((new BlogsModel())->findById($id)['autor'] ?? '')),
            'form' => $form,
            'itemId' => $id,
        ]);
    }
}
