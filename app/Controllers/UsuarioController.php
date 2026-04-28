<?php

namespace App\Controllers;

use App\Models\PasswordResetModel;
use App\Models\UsuarioModel;
use Core\Auth;
use Core\Controller;
use Core\Mailer;
use Core\NotificacionService;
use Core\Url;
use Core\Validator;
use PHPMailer\PHPMailer\Exception as MailerException;
use Throwable;

class UsuarioController extends Controller
{
    // -------------------------------------------------------------------------
    // INDEX
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $this->requireAuth();

        $usuarios   = [];
        $page       = $this->getCurrentPage();
        $perPage    = $this->getDefaultPerPage();
        $filters    = $this->getQueryParams(['q' => '']);
        $search     = (string) ($filters['q'] ?? '');
        $pagination = $this->buildPagination(0, $page, $perPage, '/usuarios', ['q' => $search]);

        try {
            $model      = new UsuarioModel();
            $totalRows  = $model->countAll($search);
            $pagination = $this->buildPagination($totalRows, $page, $perPage, '/usuarios', ['q' => $search]);
            $usuarios   = $model->paginate((int) $pagination['offset'], (int) $pagination['perPage'], $search);
        } catch (Throwable) {
            $usuarios = [];
        }

        $this->renderAdminModule('usuario/index', [
            'title'        => 'Usuarios',
            'user'         => Auth::user(),
            'usuarios'     => $usuarios,
            'pagination'   => $pagination,
            'search'       => $search,
            'searchConfig' => [
                'action'      => '/usuarios',
                'method'      => 'GET',
                'fields'      => [
                    [
                        'name'        => 'q',
                        'type'        => 'text',
                        'placeholder' => 'Buscar por usuario, nombre o grupo...',
                        'value'       => $search,
                        'icon'        => 'fa fa-search',
                    ],
                ],
                'submitLabel' => 'Buscar',
                'submitIcon'  => 'fa fa-search',
                'clearUrl'    => $search !== '' ? '/usuarios' : '',
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // AGREGAR
    // -------------------------------------------------------------------------

    public function agregar(): void
    {
        $this->requireAuth();
        $this->pageAssets['js'][] = '/assets/js/password-toggle.js';

        $model = new UsuarioModel();

        $this->renderAdminModule('usuario/agregar', [
            'title'    => 'Nuevo usuario',
            'user'     => Auth::user(),
            'error'    => null,
            'grupos'   => $model->getAllGrupos(),
            'personas' => $model->getPersonasDisponibles(),
            'form'     => [
                'usu_id'     => '',
                'usu_per_id' => '',
                'usu_estado' => 'H',
                'usu_gru_id' => '',
            ],
        ]);
    }

    public function guardar(): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model  = new UsuarioModel();
        $params = $this->request->getParams();

        $validator = Validator::make($params, [
            'usu_id'       => 'required|string|min:3|max:250',
            'usu_password' => 'required|string|min:6|max:250',
            'usu_gru_id'   => 'required|string|min:1|max:100',
            'usu_estado'   => 'required|string|min:1|max:1',
            'usu_per_id'   => 'nullable|string',
        ], [
            'usu_id'       => 'Usuario',
            'usu_password' => 'Contrasena',
            'usu_gru_id'   => 'Grupo',
            'usu_estado'   => 'Estado',
            'usu_per_id'   => 'Persona',
        ]);
        if (!$validator->passes()) {
            $this->renderAdminModule('usuario/agregar', [
                'title'    => 'Nuevo usuario',
                'user'     => Auth::user(),
                'error'    => $validator->first(),
                'errors'   => $validator->errors(),
                'grupos'   => $model->getAllGrupos(),
                'personas' => $model->getPersonasDisponibles(),
                'form'     => $params,
            ]);
            return;
        }

        // Confirm password check
        $password        = trim((string) ($params['usu_password'] ?? ''));
        $confirmPassword = trim((string) ($params['confirm_password'] ?? ''));
        if ($password !== $confirmPassword) {
            $this->renderAdminModule('usuario/agregar', [
                'title'    => 'Nuevo usuario',
                'user'     => Auth::user(),
                'error'    => 'Las contrasenas no coinciden.',
                'errors'   => [],
                'grupos'   => $model->getAllGrupos(),
                'personas' => $model->getPersonasDisponibles(),
                'form'     => $params,
            ]);
            return;
        }

        // Uniqueness check
        $usuId = trim((string) ($params['usu_id'] ?? ''));
        if ($model->existsById($usuId)) {
            $this->renderAdminModule('usuario/agregar', [
                'title'    => 'Nuevo usuario',
                'user'     => Auth::user(),
                'error'    => 'Ya existe un usuario con ese nombre de usuario.',
                'errors'   => [],
                'grupos'   => $model->getAllGrupos(),
                'personas' => $model->getPersonasDisponibles(),
                'form'     => $params,
            ]);
            return;
        }

        $model->createUsuario($params);
        $this->logAction($model->getLastActionLog(), 'CREATE');
        NotificacionService::registrar('usuarios', 'CREATE', (string) (Auth::user()['id'] ?? 'ANON'), $usuId);

        // Enviar enlace seguro de configuracion de contrasena si hay correo vinculado
        $usuId = trim((string) ($params['usu_id'] ?? ''));
        $hasPersonaLinked = trim((string) ($params['usu_per_id'] ?? '')) !== '';
        $email            = $hasPersonaLinked ? $model->getPersonaEmail($usuId) : null;
        $resetLinkSent    = false;

        if ($email !== null) {
            try {
                $persona   = $model->findById($usuId);
                $userName  = trim(
                    ((string) ($persona['per_nombre'] ?? '')) . ' ' .
                    ((string) ($persona['per_apellido'] ?? ''))
                );
                $normalizedEmail = strtolower(trim((string) $email));

                $resetModel = new PasswordResetModel();
                $token      = $resetModel->createToken($normalizedEmail);
                $resetUrl   = Url::to('/reset-password/' . $token);

                $emailHtml = $this->renderEmailView('emails/password-reset', [
                    'resetUrl'      => $resetUrl,
                    'userName'      => $userName !== '' ? $userName : $usuId,
                    'siteTitle'     => (string) ($_ENV['SITE_TITLE'] ?? 'Web Revolution'),
                    'address'       => (string) ($_ENV['ADDRESS']    ?? ''),
                    'country'       => (string) ($_ENV['COUNTRY']    ?? ''),
                    'expiryMinutes' => 60,
                ]);

                $mailer = new Mailer();
                $mailer->send(
                    $normalizedEmail,
                    'Configura tu contraseña — ' . ($_ENV['SITE_TITLE'] ?? 'Web Revolution'),
                    $emailHtml
                );
                $resetLinkSent = true;
            } catch (MailerException $e) {
                error_log('[UsuarioController::guardar] Error de mail al enviar enlace de configuracion: ' . $e->getMessage());
            } catch (Throwable $e) {
                error_log('[UsuarioController::guardar] Error al enviar enlace de configuracion: ' . $e->getMessage());
            }
        }

        $this->flashSuccess('Usuario registrado correctamente.');

        if (!$hasPersonaLinked) {
            $this->flash('warning', 'Usuario creado sin persona vinculada. Configura una persona con email para enviar enlace de restablecimiento.');
        } elseif ($email === null) {
            $this->flash('warning', 'Usuario creado, pero la persona vinculada no tiene email. No se pudo enviar enlace de configuración de contraseña.');
        } elseif (!$resetLinkSent) {
            $this->flash('warning', 'Usuario creado, pero no fue posible enviar el enlace de configuración de contraseña. Intenta reenviar desde recuperación de contraseña.');
        }

        $this->redirect('/usuarios');
    }

    // -------------------------------------------------------------------------
    // EDITAR
    // -------------------------------------------------------------------------

    public function editar(string $id): void
    {
        $this->requireAuth();
        $this->pageAssets['js'][] = '/assets/js/password-toggle.js';

        $model   = new UsuarioModel();
        $usuario = $model->findById($id);
        if (!is_array($usuario)) {
            $this->redirect('/usuarios');
            return;
        }

        $currentPerID = ($usuario['usu_per_id'] ?? '') !== '' ? (int) $usuario['usu_per_id'] : null;

        $this->renderAdminModule('usuario/editar', [
            'title'     => 'Editar usuario',
            'user'      => Auth::user(),
            'error'     => null,
            'grupos'    => $model->getAllGrupos(),
            'personas'  => $model->getPersonasDisponibles($currentPerID),
            'form'      => $usuario,
            'usuarioId' => $id,
        ]);
    }

    public function actualizar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model   = new UsuarioModel();
        $usuario = $model->findById($id);
        if (!is_array($usuario)) {
            $this->redirect('/usuarios');
            return;
        }

        $params       = $this->request->getParams();
        $currentPerID = ($usuario['usu_per_id'] ?? '') !== '' ? (int) $usuario['usu_per_id'] : null;

        $validator = Validator::make($params, [
            'usu_gru_id'   => 'required|string|min:1|max:100',
            'usu_estado'   => 'required|string|min:1|max:1',
            'usu_per_id'   => 'nullable|string',
            'usu_password' => 'nullable|string|max:250',
        ], [
            'usu_gru_id'   => 'Grupo',
            'usu_estado'   => 'Estado',
            'usu_per_id'   => 'Persona',
            'usu_password' => 'Contrasena',
        ]);
        if (!$validator->passes()) {
            $this->renderAdminModule('usuario/editar', [
                'title'     => 'Editar usuario',
                'user'      => Auth::user(),
                'error'     => $validator->first(),
                'errors'    => $validator->errors(),
                'grupos'    => $model->getAllGrupos(),
                'personas'  => $model->getPersonasDisponibles($currentPerID),
                'form'      => $params,
                'usuarioId' => $id,
            ]);
            return;
        }

        // Validate new password only if provided
        $newPassword = trim((string) ($params['usu_password'] ?? ''));
        if ($newPassword !== '') {
            if (strlen($newPassword) < 6) {
                $this->renderAdminModule('usuario/editar', [
                    'title'     => 'Editar usuario',
                    'user'      => Auth::user(),
                    'error'     => 'La contrasena debe tener al menos 6 caracteres.',
                    'errors'    => [],
                    'grupos'    => $model->getAllGrupos(),
                    'personas'  => $model->getPersonasDisponibles($currentPerID),
                    'form'      => $params,
                    'usuarioId' => $id,
                ]);
                return;
            }

            $confirmPassword = trim((string) ($params['confirm_password'] ?? ''));
            if ($newPassword !== $confirmPassword) {
                $this->renderAdminModule('usuario/editar', [
                    'title'     => 'Editar usuario',
                    'user'      => Auth::user(),
                    'error'     => 'Las contrasenas no coinciden.',
                    'errors'    => [],
                    'grupos'    => $model->getAllGrupos(),
                    'personas'  => $model->getPersonasDisponibles($currentPerID),
                    'form'      => $params,
                    'usuarioId' => $id,
                ]);
                return;
            }
        }

        $model->updateUsuario($id, $params);
        $this->logAction($model->getLastActionLog(), 'UPDATE');

        $this->flashSuccess('Usuario actualizado correctamente.');
        $this->redirect('/usuarios');
    }

    // -------------------------------------------------------------------------
    // ELIMINAR
    // -------------------------------------------------------------------------

    public function eliminar(string $id): void
    {
        $this->requireAuth();

        $model   = new UsuarioModel();
        $usuario = $model->findById($id);
        if (!is_array($usuario)) {
            $this->redirect('/usuarios');
            return;
        }

        $currentUser = Auth::user();
        $isSelf      = ($currentUser['id'] ?? '') === $id;
        $hasLogs     = $model->hasLogs($id);

        $this->renderAdminModule('usuario/eliminar', [
            'title'     => 'Eliminar usuario',
            'user'      => $currentUser,
            'form'      => $usuario,
            'usuarioId' => $id,
            'isSelf'    => $isSelf,
            'hasLogs'   => $hasLogs,
        ]);
    }

    public function borrar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model   = new UsuarioModel();
        $usuario = $model->findById($id);
        if (!is_array($usuario)) {
            $this->redirect('/usuarios');
            return;
        }

        $currentUser = Auth::user();
        if (($currentUser['id'] ?? '') === $id) {
            $this->flashError('No puedes eliminar tu propio usuario.');
            $this->redirect('/usuarios/' . urlencode($id) . '/eliminar');
            return;
        }

        if ($model->hasLogs($id)) {
            $this->flashError('No se puede eliminar el usuario porque tiene acciones registradas en el sistema.');
            $this->redirect('/usuarios/' . urlencode($id) . '/eliminar');
            return;
        }

        $model->deleteUsuario($id);
        $this->logAction($model->getLastActionLog(), 'DELETE');

        $this->flashSuccess('Usuario eliminado correctamente.');
        $this->redirect('/usuarios');
    }

}
