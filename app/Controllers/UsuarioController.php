<?php

namespace App\Controllers;

use App\Models\PasswordResetModel;
use App\Models\UsuarioModel;
use Core\Auth;
use Core\Controller;
use Core\EmailMessages;
use Core\FlashMessages;
use Core\LogMessages;
use Core\Mailer;
use Core\NotificacionService;
use Core\UiMessages;
use Core\Url;
use Core\ValidationMessages;
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
            'title'        => UiMessages::USUARIO_INDEX_TITLE,
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
            'title'    => UiMessages::USUARIO_CREATE_TITLE,
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
                'title'    => UiMessages::USUARIO_CREATE_TITLE,
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
                'title'    => UiMessages::USUARIO_CREATE_TITLE,
                'user'     => Auth::user(),
                'error'    => ValidationMessages::USUARIO_PASSWORDS_DO_NOT_MATCH,
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
                'title'    => UiMessages::USUARIO_CREATE_TITLE,
                'user'     => Auth::user(),
                'error'    => ValidationMessages::USUARIO_ALREADY_EXISTS,
                'errors'   => [],
                'grupos'   => $model->getAllGrupos(),
                'personas' => $model->getPersonasDisponibles(),
                'form'     => $params,
            ]);
            return;
        }

        $model->createUsuario($params);
        $this->logAction($model->getLastActionLog(), 'CREATE');
        $actorId = (string) (Auth::user()['id'] ?? 'ANON');

        // Auditoria global del evento
        NotificacionService::registrar('usuarios', 'CREATE', $actorId, $usuId);
        // Notificacion personal al usuario creado
        NotificacionService::registrar(
            'usuarios',
            'CREATE',
            $actorId,
            $usuId,
            'Tu cuenta fue creada. Si tienes correo registrado, recibirás un enlace para configurar tu contraseña.',
            $usuId
        );

        // Enviar enlace seguro de configuracion de contrasena si hay correo vinculado
        $usuId = trim((string) ($params['usu_id'] ?? ''));
        $hasPersonaLinked = trim((string) ($params['usu_per_id'] ?? '')) !== '';
        $email            = $hasPersonaLinked ? $model->getPersonaEmail($usuId) : null;
        $resetLinkSent    = false;
        $normalizedEmail  = $email !== null ? strtolower(trim((string) $email)) : '';

        if ($email !== null) {
            try {
                $persona   = $model->findById($usuId);
                $userName  = trim(
                    ((string) ($persona['per_nombre'] ?? '')) . ' ' .
                    ((string) ($persona['per_apellido'] ?? ''))
                );

                $resetModel = new PasswordResetModel();
                $token      = $resetModel->createToken($normalizedEmail);
                $resetUrl   = Url::to('/reset-password/' . $token);

                $siteTitle = EmailMessages::siteTitle();

                $emailHtml = $this->emailTemplatesRenderer->render(EmailMessages::TEMPLATE_PASSWORD_RESET, [
                    'resetUrl'      => $resetUrl,
                    'userName'      => $userName !== '' ? $userName : $usuId,
                    'siteTitle'     => $siteTitle,
                    'address'       => (string) ($_ENV['ADDRESS']    ?? ''),
                    'country'       => (string) ($_ENV['COUNTRY']    ?? ''),
                    'expiryMinutes' => 60,
                ]);

                $mailer = new Mailer();
                $mailer->send(
                    $normalizedEmail,
                    EmailMessages::setupPasswordSubject($siteTitle),
                    $emailHtml
                );
                $resetLinkSent = true;
            } catch (MailerException $e) {
                error_log(LogMessages::usuarioGuardarMailerErrorForRecipient($e, $normalizedEmail));
            } catch (Throwable $e) {
                error_log(LogMessages::usuarioGuardarErrorForRecipient($e, $normalizedEmail));
            }
        }

        $this->flashSuccess(FlashMessages::USUARIO_CREATED);

        if (!$hasPersonaLinked) {
            $this->flash('warning', FlashMessages::USUARIO_CREATED_NO_PERSONA);
        } elseif ($email === null) {
            $this->flash('warning', FlashMessages::USUARIO_CREATED_NO_EMAIL);
        } elseif (!$resetLinkSent) {
            $this->flash('warning', FlashMessages::USUARIO_CREATED_EMAIL_SEND_FAILED);
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
            'title'     => UiMessages::USUARIO_EDIT_TITLE,
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
                'title'     => UiMessages::USUARIO_EDIT_TITLE,
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
                    'title'     => UiMessages::USUARIO_EDIT_TITLE,
                    'user'      => Auth::user(),
                    'error'     => ValidationMessages::USUARIO_PASSWORD_MIN_6,
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
                    'title'     => UiMessages::USUARIO_EDIT_TITLE,
                    'user'      => Auth::user(),
                    'error'     => ValidationMessages::USUARIO_PASSWORDS_DO_NOT_MATCH,
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
        $actorId = (string) (Auth::user()['id'] ?? 'ANON');

        // Auditoria global del evento
        NotificacionService::registrar('usuarios', 'UPDATE', $actorId, $id);
        // Notificacion personal al usuario afectado
        NotificacionService::registrar(
            'usuarios',
            'UPDATE',
            $actorId,
            $id,
            'Tu cuenta fue actualizada por un administrador.',
            $id
        );

        $this->flashSuccess(FlashMessages::USUARIO_UPDATED);
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
            'title'     => UiMessages::USUARIO_DELETE_TITLE,
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
            $this->flashError(FlashMessages::USUARIO_DELETE_SELF_FORBIDDEN);
            $this->redirect('/usuarios/' . urlencode($id) . '/eliminar');
            return;
        }

        if ($model->hasLogs($id)) {
            $this->flashError(FlashMessages::USUARIO_DELETE_HAS_LOGS_FORBIDDEN);
            $this->redirect('/usuarios/' . urlencode($id) . '/eliminar');
            return;
        }

        $model->deleteUsuario($id);
        $this->logAction($model->getLastActionLog(), 'DELETE');
        $actorId = (string) (Auth::user()['id'] ?? 'ANON');

        // Auditoria global del evento
        NotificacionService::registrar('usuarios', 'DELETE', $actorId, $id);

        $this->flashSuccess(FlashMessages::USUARIO_DELETED);
        $this->redirect('/usuarios');
    }

}
