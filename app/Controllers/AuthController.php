<?php

namespace App\Controllers;

use App\Models\UserModel;
use Core\Auth;
use Core\Controller;
use Core\RateLimiter;
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
}