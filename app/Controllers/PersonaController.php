<?php

namespace App\Controllers;

use App\Models\PersonaModel;
use Core\Auth;
use Core\Controller;
use Core\ImageUploader;
use Core\Validator;
use Throwable;

class PersonaController extends Controller
{
    // Upload configuration — adjust THUMB_SIZES to add or remove thumbnail dimensions.
    private const UPLOAD_MODULE  = 'personas';
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const MAX_FILE_SIZE = 2097152; // 2 MB
    private const THUMB_SIZES   = [
        ['w' => 130, 'h' => 130, 'mode' => 'crop'], // avatar (exact square)
        ['w' => 300, 'h' => 300, 'mode' => 'fit'],  // medium (proportional)
    ];

    // -------------------------------------------------------------------------
    // INDEX
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $this->requireAuth();

        $personas   = [];
        $page       = $this->getCurrentPage();
        $perPage    = $this->getDefaultPerPage();
        $filters    = $this->getQueryParams(['q' => '']);
        $search     = (string) ($filters['q'] ?? '');
        $pagination = $this->buildPagination(0, $page, $perPage, '/personas', ['q' => $search]);

        try {
            $model      = new PersonaModel();
            $totalRows  = $model->countAll($search);
            $pagination = $this->buildPagination($totalRows, $page, $perPage, '/personas', ['q' => $search]);
            $personas   = $model->paginate((int) $pagination['offset'], (int) $pagination['perPage'], $search);
        } catch (Throwable) {
            $personas = [];
        }

        $this->renderAdminModule('persona/index', [
            'title'      => 'Personas',
            'user'       => Auth::user(),
            'personas'   => $personas,
            'pagination' => $pagination,
            'search'     => $search,
            'searchConfig' => [
                'action'      => '/personas',
                'method'      => 'GET',
                'fields'      => [
                    [
                        'name'        => 'q',
                        'type'        => 'text',
                        'placeholder' => 'Buscar por nombre, apellido, CI o email...',
                        'value'       => $search,
                        'icon'        => 'fa fa-search',
                    ],
                ],
                'submitLabel' => 'Buscar',
                'submitIcon'  => 'fa fa-search',
                'clearUrl'    => $search !== '' ? '/personas' : '',
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // AGREGAR
    // -------------------------------------------------------------------------

    public function agregar(): void
    {
        $this->requireAuth();
        $this->pageAssets['js'][] = '/assets/js/image-upload.js';

        $this->renderAdminModule('persona/agregar', [
            'title' => 'Nueva persona',
            'user'  => Auth::user(),
            'error' => null,
            'form'  => [
                'per_nombre'           => '',
                'per_apellido'         => '',
                'per_email'            => '',
                'per_telefono'         => '',
                'per_direccion'        => '',
                'per_ci'               => '',
                'per_fecha_nacimiento' => '',
                'per_sexo'             => 'M',
                'per_estado'           => 'A',
            ],
        ]);
    }

    public function guardar(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $this->pageAssets['js'][] = '/assets/js/image-upload.js';

        $model  = new PersonaModel();
        $params = $this->request->getParams();

        $validator = Validator::make($params, [
            'per_nombre'           => 'required|string|min:2|max:250',
            'per_apellido'         => 'required|string|min:2|max:250',
            'per_sexo'             => 'required|string|min:1|max:1',
            'per_estado'           => 'required|string|min:1|max:1',
            'per_email'            => 'nullable|string|max:250',
            'per_telefono'         => 'nullable|string|max:50',
            'per_direccion'        => 'nullable|string',
            'per_ci'               => 'nullable|string|max:255',
            'per_fecha_nacimiento' => 'nullable|string',
        ], [
            'per_nombre'           => 'Nombre',
            'per_apellido'         => 'Apellido',
            'per_sexo'             => 'Sexo',
            'per_estado'           => 'Estado',
            'per_email'            => 'Email',
            'per_telefono'         => 'Telefono',
            'per_direccion'        => 'Direccion',
            'per_ci'               => 'CI',
            'per_fecha_nacimiento' => 'Fecha de nacimiento',
        ]);
        $validator->passes();

        if ($validator->first() !== null) {
            $this->renderAdminModule('persona/agregar', [
                'title'  => 'Nueva persona',
                'user'   => Auth::user(),
                'error'  => $validator->first(),
                'errors' => $validator->errors(),
                'form'   => $params,
            ]);
            return;
        }

        // Validate email format
        $email = trim((string) ($params['per_email'] ?? ''));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->renderAdminModule('persona/agregar', [
                'title'  => 'Nueva persona',
                'user'   => Auth::user(),
                'error'  => 'El formato del email no es valido.',
                'errors' => [],
                'form'   => $params,
            ]);
            return;
        }

        // CI uniqueness check
        $ci = trim((string) ($params['per_ci'] ?? ''));
        if ($ci !== '' && $model->existsByCi($ci)) {
            $this->renderAdminModule('persona/agregar', [
                'title'  => 'Nueva persona',
                'user'   => Auth::user(),
                'error'  => 'Ya existe una persona con ese numero de CI.',
                'errors' => [],
                'form'   => $params,
            ]);
            return;
        }

        // Handle photo upload
        $fotoPath = '';
        $upload = $this->makeUploader()->handle('per_foto');
        if ($upload->hasError()) {
            $this->renderAdminModule('persona/agregar', [
                'title'  => 'Nueva persona',
                'user'   => Auth::user(),
                'error'  => $upload->getError(),
                'errors' => [],
                'form'   => $params,
            ]);
            return;
        }
        $fotoPath = $upload->getPath(); // '' if no file submitted

        $params['per_foto'] = $fotoPath;
        $perId = $model->createPersona($params);
        $this->logAction($model->getLastSqlLog(), 'CREATE');

        $this->flashSuccess('Persona registrada correctamente.');
        $this->redirect('/personas');
    }

    // -------------------------------------------------------------------------
    // EDITAR
    // -------------------------------------------------------------------------

    public function editar(string $id): void
    {
        $this->requireAuth();
        $this->pageAssets['js'][] = '/assets/js/image-upload.js';

        $model   = new PersonaModel();
        $persona = $model->findById($id);
        if (!is_array($persona)) {
            $this->redirect('/personas');
            return;
        }

        $this->renderAdminModule('persona/editar', [
            'title'     => 'Editar persona',
            'user'      => Auth::user(),
            'error'     => null,
            'form'      => $persona,
            'personaId' => $id,
        ]);
    }

    public function actualizar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $this->pageAssets['js'][] = '/assets/js/image-upload.js';

        $model   = new PersonaModel();
        $persona = $model->findById($id);
        if (!is_array($persona)) {
            $this->redirect('/personas');
            return;
        }

        $params = $this->request->getParams();

        $validator = Validator::make($params, [
            'per_nombre'           => 'required|string|min:2|max:250',
            'per_apellido'         => 'required|string|min:2|max:250',
            'per_sexo'             => 'required|string|min:1|max:1',
            'per_estado'           => 'required|string|min:1|max:1',
            'per_email'            => 'nullable|string|max:250',
            'per_telefono'         => 'nullable|string|max:50',
            'per_direccion'        => 'nullable|string',
            'per_ci'               => 'nullable|string|max:255',
            'per_fecha_nacimiento' => 'nullable|string',
        ], [
            'per_nombre'           => 'Nombre',
            'per_apellido'         => 'Apellido',
            'per_sexo'             => 'Sexo',
            'per_estado'           => 'Estado',
            'per_email'            => 'Email',
            'per_telefono'         => 'Telefono',
            'per_direccion'        => 'Direccion',
            'per_ci'               => 'CI',
            'per_fecha_nacimiento' => 'Fecha de nacimiento',
        ]);
        $validator->passes();

        if ($validator->first() !== null) {
            $this->renderAdminModule('persona/editar', [
                'title'     => 'Editar persona',
                'user'      => Auth::user(),
                'error'     => $validator->first(),
                'errors'    => $validator->errors(),
                'form'      => $params,
                'personaId' => $id,
            ]);
            return;
        }

        // Validate email format
        $email = trim((string) ($params['per_email'] ?? ''));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->renderAdminModule('persona/editar', [
                'title'     => 'Editar persona',
                'user'      => Auth::user(),
                'error'     => 'El formato del email no es valido.',
                'errors'    => [],
                'form'      => $params,
                'personaId' => $id,
            ]);
            return;
        }

        // CI uniqueness check (excluding current record)
        $ci = trim((string) ($params['per_ci'] ?? ''));
        if ($ci !== '' && $model->existsByCi($ci, $id)) {
            $this->renderAdminModule('persona/editar', [
                'title'     => 'Editar persona',
                'user'      => Auth::user(),
                'error'     => 'Ya existe una persona con ese numero de CI.',
                'errors'    => [],
                'form'      => $params,
                'personaId' => $id,
            ]);
            return;
        }

        // Handle photo upload; keep existing if no new file uploaded
        $fotoPath = (string) ($persona['per_foto'] ?? '');
        $upload   = $this->makeUploader()->handle('per_foto');
        if ($upload->hasError()) {
            $this->renderAdminModule('persona/editar', [
                'title'     => 'Editar persona',
                'user'      => Auth::user(),
                'error'     => $upload->getError(),
                'errors'    => [],
                'form'      => $params,
                'personaId' => $id,
            ]);
            return;
        }
        if (!$upload->isEmpty()) {
            $fotoPath = $upload->getPath();
        }

        $params['per_foto'] = $fotoPath;
        $model->updatePersona($id, $params);
        $this->logAction($model->getLastSqlLog(), 'UPDATE');

        $this->flashSuccess('Persona actualizada correctamente.');
        $this->redirect('/personas');
    }

    // -------------------------------------------------------------------------
    // ELIMINAR
    // -------------------------------------------------------------------------

    public function eliminar(string $id): void
    {
        $this->requireAuth();

        $model   = new PersonaModel();
        $persona = $model->findById($id);
        if (!is_array($persona)) {
            $this->redirect('/personas');
            return;
        }

        $this->renderAdminModule('persona/eliminar', [
            'title'             => 'Eliminar persona',
            'user'              => Auth::user(),
            'form'              => $persona,
            'personaId'         => $id,
            'linkedToUsuario'   => $model->isLinkedToUsuario($id),
        ]);
    }

    public function borrar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model   = new PersonaModel();
        $persona = $model->findById($id);
        if (!is_array($persona)) {
            $this->redirect('/personas');
            return;
        }

        if ($model->isLinkedToUsuario($id)) {
            $this->flashError('No se puede eliminar la persona porque tiene un usuario asociado. Elimine primero el usuario.');
            $this->redirect('/personas/' . urlencode($id) . '/eliminar');
            return;
        }

        $model->deletePersona($id);
        $this->logAction($model->getLastSqlLog(), 'DELETE');

        $this->flashSuccess('Persona eliminada correctamente.');
        $this->redirect('/personas');
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    /** Returns a configured ImageUploader for the persona module. */
    private function makeUploader(): ImageUploader
    {
        return new ImageUploader([
            'module'       => self::UPLOAD_MODULE,
            'allowedTypes' => self::ALLOWED_TYPES,
            'maxSize'      => self::MAX_FILE_SIZE,
            'thumbs'       => self::THUMB_SIZES,
        ]);
    }
}
