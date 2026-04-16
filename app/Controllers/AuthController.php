<?php

namespace App\Controllers;

use App\Models\PasswordResetModel;
use App\Models\UserModel;
use Core\Auth;
use Core\Controller;
use Core\Mailer;
use Core\RateLimiter;
use PHPMailer\PHPMailer\Exception as MailerException;
use Throwable;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->logAction('Redireccion login a dashboard por sesion activa', 'AUTH');
            $this->redirect('/dashboard');
        }

        //$this->logAction('Acceso pantalla login', 'AUTH');
        $loginView = $this->resolveTemplate('login', 'auth/login');

        $this->render($loginView, [
            'title' => 'Iniciar Sesion',
            'error' => null,
        ]);
    }

    public function login(): void
    {
        $this->requireCsrf('/login');

        $ip          = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $loginView   = $this->resolveTemplate('login', 'auth/login');
        $rateLimiter = null;

        try {
            $rateLimiter = new RateLimiter();
        } catch (Throwable) {
            // Si la BD falla, no bloqueamos el acceso
        }

        if ($rateLimiter !== null && $rateLimiter->tooManyAttempts($ip)) {
            $this->logAction('Login bloqueado por rate limit: ' . $ip, 'AUTH_BLOCK');
            $this->render($loginView, [
                'title' => 'Iniciar Sesion',
                'error' => 'Demasiados intentos fallidos. Espera ' . $rateLimiter->lockoutMinutes() . ' minutos e intenta de nuevo.',
            ]);
            return;
        }

        $params   = $this->request->getParams();
        $username = trim((string) ($params['username'] ?? ''));
        $password = (string) ($params['password'] ?? '');

        if ($username === '' || $password === '') {
            $this->logAction('Intento login sin usuario/password', 'AUTH_FAIL');
            $this->render($loginView, [
                'title' => 'Iniciar Sesion',
                'error' => 'Usuario y password son obligatorios.',
            ]);
            return;
        }

        $user = null;
        try {
            $userModel = new UserModel();
            $user      = $userModel->authenticate($username, $password);
        } catch (Throwable) {
            $user = null;
        }

        if (is_array($user)) {
            $rateLimiter?->clear($ip);
            Auth::login($user);
            $this->logAction('Ingreso al Sistema', 'AUTH_OK');
            $this->redirect('/dashboard');
            return;
        }

        $rateLimiter?->hit($ip);
        $remaining = $rateLimiter?->remainingAttempts($ip);

        $this->logAction('Login fallido: ' . $username, 'AUTH_FAIL');

        $error = 'Credenciales invalidas.';
        if ($remaining !== null && $remaining <= 2 && $remaining > 0) {
            $error .= ' Te queda' . ($remaining === 1 ? '' : 'n') . ' ' . $remaining . ' intento' . ($remaining === 1 ? '' : 's') . '.';
        }

        $this->render($loginView, [
            'title' => 'Iniciar Sesion',
            'error' => $error,
        ]);
    }

    public function logout(): void
    {
        $this->requireCsrf('/dashboard');
        $this->logAction('Salir del Sistema', 'AUTH');
        Auth::logout();
        $this->redirect('/login');
    }

    // -------------------------------------------------------------------------
    // Recuperacion de contrasena
    // -------------------------------------------------------------------------

    public function showForgotPassword(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $this->render($this->resolveTemplate('login', 'forgot-password'), [
            'title' => 'Recuperar Contraseña',
        ]);
    }

    public function processForgotPassword(): void
    {
        $this->requireCsrf('/forgot-password');

        $view = $this->resolveTemplate('login', 'forgot-password');

        // Rate limit por IP
        $ip          = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $rateLimiter = null;
        try {
            $rateLimiter = new RateLimiter();
        } catch (Throwable) {
        }

        if ($rateLimiter !== null && $rateLimiter->tooManyAttempts($ip)) {
            $this->render($view, [
                'title' => 'Recuperar Contraseña',
                'error' => 'Demasiadas solicitudes. Espera ' . $rateLimiter->lockoutMinutes() . ' minutos e intenta de nuevo.',
            ]);
            return;
        }

        $params = $this->request->getParams();
        $email  = strtolower(trim((string) ($params['email'] ?? '')));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render($view, [
                'title' => 'Recuperar Contraseña',
                'error' => 'Ingresa un correo electrónico válido.',
                'email' => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
            ]);
            return;
        }

        // Mensaje neutral: no revelar si el email existe o no
        $successMsg = 'Si ese correo está registrado, recibirás un enlace en los próximos minutos.';

        try {
            $model = new PasswordResetModel();
            $user  = $model->findUserByEmail($email);

            if ($user !== null) {
                $token    = $model->createToken($email);
                $siteRoot = rtrim((string) ($_ENV['SITE_ROOT'] ?? ''), '/');
                $resetUrl = $siteRoot . '/reset-password/' . $token;

                $emailHtml = $this->renderEmailView('emails/password-reset', [
                    'resetUrl'      => $resetUrl,
                    'userName'      => (string) ($user['per_nombre'] ?? 'Usuario'),
                    'siteTitle'     => (string) ($_ENV['SITE_TITLE'] ?? 'Web Revolution'),
                    'address'       => (string) ($_ENV['ADDRESS']    ?? ''),
                    'country'       => (string) ($_ENV['COUNTRY']    ?? ''),
                    'expiryMinutes' => 60,
                ]);

                $mailer = new Mailer();
                $mailer->send(
                    $email,
                    'Recuperación de contraseña — ' . ($_ENV['SITE_TITLE'] ?? 'Web Revolution'),
                    $emailHtml
                );

                $this->logAction('Solicitud recuperacion contrasena: ' . $email, 'AUTH_RESET');
            }
        } catch (MailerException $e) {
            error_log('[AuthController::processForgotPassword] Mailer error: ' . $e->getMessage());
            // Mostramos exito igual para no revelar info, pero logueamos el fallo
        } catch (Throwable $e) {
            error_log('[AuthController::processForgotPassword] Error: ' . $e->getMessage());
        }

        $rateLimiter?->hit($ip);

        $this->render($view, [
            'title'   => 'Recuperar Contraseña',
            'success' => $successMsg,
        ]);
    }

    public function showResetPassword(string $token): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $view = $this->resolveTemplate('login', 'reset-password');
        $data = ['title' => 'Nueva Contraseña', 'token' => $token];

        try {
            $model = new PasswordResetModel();
            $row   = $model->findValidToken($token);

            if ($row === null) {
                $data['tokenInvalid'] = 'Este enlace no es válido o ya expiró. Solicita uno nuevo.';
            }
        } catch (Throwable) {
            $data['tokenInvalid'] = 'Ocurrió un error al verificar el enlace. Intenta de nuevo.';
        }

        $this->render($view, $data);
    }

    public function processResetPassword(string $token): void
    {
        $this->requireCsrf('/login');

        $view = $this->resolveTemplate('login', 'reset-password');

        $renderError = function (string $msg) use ($view, $token): void {
            $this->render($view, [
                'title' => 'Nueva Contraseña',
                'token' => $token,
                'error' => $msg,
            ]);
        };

        try {
            $model = new PasswordResetModel();
            $row   = $model->findValidToken($token);

            if ($row === null) {
                $this->render($view, [
                    'title'        => 'Nueva Contraseña',
                    'token'        => $token,
                    'tokenInvalid' => 'Este enlace no es válido o ya expiró. Solicita uno nuevo.',
                ]);
                return;
            }

            $params          = $this->request->getParams();
            $password        = (string) ($params['password']         ?? '');
            $passwordConfirm = (string) ($params['password_confirm'] ?? '');

            if (strlen($password) < 8) {
                $renderError('La contraseña debe tener al menos 8 caracteres.');
                return;
            }

            if ($password !== $passwordConfirm) {
                $renderError('Las contraseñas no coinciden.');
                return;
            }

            $userId = (string) ($row['email'] ?? '');

            // Obtener usu_id a partir del email almacenado en el token
            $userModel = new PasswordResetModel();
            $user      = $userModel->findUserByEmail((string) ($row['email'] ?? ''));

            if ($user === null) {
                $renderError('No se encontró la cuenta asociada. Contacta al administrador.');
                return;
            }

            $model->updatePassword((string) $user['usu_id'], $password);
            $model->markTokenUsed((int) $row['id']);

            $this->logAction('Contrasena restablecida para: ' . ($row['email'] ?? ''), 'AUTH_RESET_OK');
        } catch (Throwable $e) {
            error_log('[AuthController::processResetPassword] Error: ' . $e->getMessage());
            $renderError('Ocurrió un error al guardar la contraseña. Intenta de nuevo.');
            return;
        }

        $this->flashSuccess('Contraseña actualizada correctamente. Ya puedes iniciar sesión.');
        $this->redirect('/login');
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Renderiza una vista de email y retorna el HTML como string.
     */
    private function renderEmailView(string $viewPath, array $data = []): string
    {
        $fullPath = dirname(__DIR__) . '/Views/' . ltrim($viewPath, '/') . '.php';
        if (!is_file($fullPath)) {
            throw new \RuntimeException("Email view not found: {$fullPath}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $fullPath;
        return (string) ob_get_clean();
    }
}