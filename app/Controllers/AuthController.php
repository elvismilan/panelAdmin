<?php

namespace App\Controllers;

use App\Models\PasswordResetModel;
use App\Models\UserModel;
use Core\Auth;
use Core\Controller;
use Core\FlashMessages;
use Core\LogMessages;
use Core\Mailer;
use Core\RateLimiter;
use Core\Url;
use Core\ValidationMessages;
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
                'error' => ValidationMessages::authLoginRateLimit($rateLimiter->lockoutMinutes()),
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
                'error' => ValidationMessages::AUTH_REQUIRED_CREDENTIALS,
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

        $error = ValidationMessages::authInvalidCredentialsWithRemaining($remaining);

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
                'error' => ValidationMessages::forgotRateLimit($rateLimiter->lockoutMinutes()),
            ]);
            return;
        }

        $params = $this->request->getParams();
        $email  = strtolower(trim((string) ($params['email'] ?? '')));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render($view, [
                'title' => 'Recuperar Contraseña',
                'error' => ValidationMessages::FORGOT_EMAIL_INVALID,
                'email' => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
            ]);
            return;
        }

        // Mensaje neutral: no revelar si el email existe o no
        $successMsg = ValidationMessages::FORGOT_SUCCESS_NEUTRAL;

        try {
            $model = new PasswordResetModel();
            $user  = $model->findUserByEmail($email);

            if ($user !== null) {
                $token    = $model->createToken($email);
                $resetUrl = Url::to('/reset-password/' . $token);

                $emailHtml = $this->emailTemplatesRenderer->render('emails/password-reset', [
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
            error_log(LogMessages::authForgotMailerError($e));
            // Mostramos exito igual para no revelar info, pero logueamos el fallo
        } catch (Throwable $e) {
            error_log(LogMessages::authForgotError($e));
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
                $data['tokenInvalid'] = ValidationMessages::RESET_TOKEN_INVALID;
            }
        } catch (Throwable) {
            $data['tokenInvalid'] = ValidationMessages::RESET_TOKEN_CHECK_ERROR;
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

            $params          = $this->request->getParams();
            $password        = (string) ($params['password']         ?? '');
            $passwordConfirm = (string) ($params['password_confirm'] ?? '');

            if (strlen($password) < 8) {
                $renderError(ValidationMessages::RESET_PASSWORD_MIN_8);
                return;
            }

            if ($password !== $passwordConfirm) {
                $renderError(ValidationMessages::RESET_PASSWORDS_DO_NOT_MATCH);
                return;
            }

            $tokenEmail = $model->consumeTokenAndUpdatePassword($token, $password);
            if ($tokenEmail === null) {
                $this->render($view, [
                    'title'        => 'Nueva Contraseña',
                    'token'        => $token,
                    'tokenInvalid' => ValidationMessages::RESET_TOKEN_INVALID_USED_OR_EXPIRED,
                ]);
                return;
            }

            $this->logAction('Contrasena restablecida para: ' . $tokenEmail, 'AUTH_RESET_OK');
        } catch (Throwable $e) {
            error_log(LogMessages::authResetError($e));
            $renderError(ValidationMessages::RESET_SAVE_ERROR);
            return;
        }

        $this->flashSuccess(FlashMessages::AUTH_PASSWORD_UPDATED);
        $this->redirect('/login');
    }

}