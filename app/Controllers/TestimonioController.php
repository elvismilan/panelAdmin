<?php

namespace App\Controllers;

use App\Models\TestimonioModel;
use PanelAdmin\Core\Auth;
use PanelAdmin\Core\Controller;
use App\Messages\FlashMessages;
use PanelAdmin\Core\NotificacionService;
use App\Messages\UiMessages;
use App\Messages\ValidationMessages;
use PanelAdmin\Core\Validator;
use Throwable;

class TestimonioController extends Controller
{
    private const ESTADOS = ['H', 'D'];

    /** Largos maximos: solo el nombre (sin apellido) y un texto que entre en el carrusel. */
    public const NOMBRE_MAX = 40;
    public const DETALLE_MAX = 600;
    public const CONTEXTO_MAX = 60;

    public function index(): void
    {
        $this->requireAuth();
        $this->addJs('/assets/js/admin-reorder.js?v=20261125');

        $list = $this->searchableIndex('/testimonios', TestimonioModel::class, 'Buscar por nombre o detalle...');

        $this->renderAdminModule('testimonio/index', [
            'title' => UiMessages::TESTIMONIO_INDEX_TITLE,
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

        $this->renderAdminModule('testimonio/agregar', [
            'title' => UiMessages::TESTIMONIO_CREATE_TITLE,
            'user' => Auth::user(),
            'error' => null,
            'form' => [
                'nombre' => '',
                'contexto' => '',
                'detalle' => '',
                'estado' => 'H',
            ],
        ]);
    }

    public function guardar(): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new TestimonioModel();
        $params = $this->normalizeParams($this->request->getParams());

        $renderError = function (?string $error, array $params, array $errors = []): void {
            $this->renderAdminModule('testimonio/agregar', [
                'title' => UiMessages::TESTIMONIO_CREATE_TITLE,
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

        $testimonioId = $model->createRecord($params);
        $this->logAction($model->getLastActionLog(), 'CREATE');
        NotificacionService::registrar('testimonios', 'CREATE', (string) (Auth::user()['id'] ?? 'ANON'), $testimonioId);

        $this->flashSuccess(FlashMessages::TESTIMONIO_CREATED);
        $this->redirect('/testimonios');
    }

    public function editar(string $id): void
    {
        $this->requireAuth();

        $item = (new TestimonioModel())->findById($id);
        if (!is_array($item)) {
            $this->redirect('/testimonios');
            return;
        }

        $this->renderAdminModule('testimonio/editar', [
            'title' => UiMessages::TESTIMONIO_EDIT_TITLE,
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

        $model = new TestimonioModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/testimonios');
            return;
        }

        $params = $this->normalizeParams($this->request->getParams());

        $renderError = function (?string $error, array $params, array $errors = []) use ($id, $item): void {
            $this->renderAdminModule('testimonio/editar', [
                'title' => UiMessages::TESTIMONIO_EDIT_TITLE,
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

        $model->updateRecord($id, $params);
        $this->logAction($model->getLastActionLog(), 'UPDATE');
        NotificacionService::registrar('testimonios', 'UPDATE', (string) (Auth::user()['id'] ?? 'ANON'), $id);

        $this->flashSuccess(FlashMessages::TESTIMONIO_UPDATED);
        $this->redirect('/testimonios');
    }

    public function eliminar(string $id): void
    {
        $this->requireAuth();

        $model = new TestimonioModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/testimonios');
            return;
        }

        $this->renderAdminModule('testimonio/eliminar', [
            'title' => UiMessages::TESTIMONIO_DELETE_TITLE,
            'user' => Auth::user(),
            'form' => $item,
            'itemId' => $id,
        ]);
    }

    public function borrar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new TestimonioModel();
        $item = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/testimonios');
            return;
        }

        // Soft delete: se conservan el registro y sus archivos por si se restaura.
        $model->deleteRecord($id);
        $this->logAction($model->getLastActionLog(), 'DELETE');
        NotificacionService::registrar('testimonios', 'DELETE', (string) (Auth::user()['id'] ?? 'ANON'), $id);

        $this->flashSuccess(FlashMessages::TESTIMONIO_DELETED);
        $this->redirect('/testimonios');
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

        $model = new TestimonioModel();
        if (!is_array($model->findById($id))) {
            $this->jsonResponse(['ok' => false, 'error' => 'Registro no encontrado.'], 404);
            return;
        }

        try {
            $orden = $model->moveRecord($id, $direction);
        } catch (Throwable $e) {
            error_log('TestimonioController::ordenar error: ' . $e->getMessage());
            $this->jsonResponse(['ok' => false, 'error' => 'No se pudo actualizar el orden.'], 500);
            return;
        }

        if ($orden === null) {
            $this->jsonResponse(['ok' => true, 'moved' => false]);
            return;
        }

        $this->logAction($model->getLastActionLog(), 'UPDATE');

        $this->jsonResponse(['ok' => true, 'moved' => true, 'orden' => $orden]);
    }

    // ------------------------------------------------------------------ Helpers

    /**
     * Quita el orden, recorta el nombre y el detalle y unifica los saltos de linea (\r\n -> \n),
     * para que el largo que valida el servidor sea el mismo que cuenta el navegador.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function normalizeParams(array $params): array
    {
        // El orden no se edita en el formulario: se cambia con las flechas del listado.
        unset($params['orden']);

        foreach (['nombre', 'contexto', 'detalle'] as $field) {
            if (isset($params[$field]) && is_string($params[$field])) {
                $params[$field] = trim(str_replace(["\r\n", "\r"], "\n", $params[$field]));
            }
        }

        return $params;
    }

    /** @return array{0: bool, 1: ?string, 2: array<string, array<int, string>>} */
    private function validateForm(array $params): array
    {
        $validator = Validator::make($params, [
            'nombre' => 'required|string|min:2|max:' . self::NOMBRE_MAX,
            'contexto' => 'nullable|string|max:' . self::CONTEXTO_MAX,
            'detalle' => 'required|string|min:5|max:' . self::DETALLE_MAX,
            'estado' => 'required|string|min:1|max:1',
        ], [
            'nombre' => 'Nombre',
            'contexto' => 'Contexto',
            'detalle' => 'Detalle',
            'estado' => 'Estado',
        ]);

        $passes = $validator->passes();
        $errors = $validator->errors();
        $error = $validator->first();

        if ($passes && !in_array((string) ($params['estado'] ?? ''), self::ESTADOS, true)) {
            $passes = false;
            $error = ValidationMessages::TESTIMONIO_ESTADO_INVALIDO;
            $errors['estado'] = [$error];
        }

        return [$passes, $error, $errors];
    }
}
