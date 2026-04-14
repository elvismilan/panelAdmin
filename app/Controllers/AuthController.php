<?php

namespace App\Controllers;

use App\Models\UserModel;
use Core\Auth;
use Core\Controller;
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
        $loginView = $this->resolveTemplate('login', 'login/index');

        $this->render($loginView, [
            'title' => 'Iniciar Sesion',
            'error' => null,
        ]);
    }

    public function login(): void
    {
        $params = $this->request->getParams();
        $username = trim((string) ($params['username'] ?? ''));
        $password = (string) ($params['password'] ?? '');

        if ($username === '' || $password === '') {
            $this->logAction('Intento login sin usuario/password', 'AUTH_FAIL');
            $this->render($this->resolveTemplate('login', 'login/index'), [
                'title' => 'Iniciar Sesion',
                'error' => 'Usuario y password son obligatorios.',
            ]);
            return;
        }

        $user = null;
        try {
            $userModel = new UserModel();
            $user = $userModel->authenticate($username, $password);
        } catch (Throwable) {
            $user = null;
        }

        if (is_array($user)) {
            Auth::login($user);
            $this->logAction('Ingreso al Sistema', 'AUTH_OK');
            $this->redirect('/dashboard');
            return;
        }

        $this->logAction('Login fallido: ' . $username, 'AUTH_FAIL');
        $this->render($this->resolveTemplate('login', 'login/index'), [
            'title' => 'Iniciar Sesion',
            'error' => 'Credenciales invalidas.',
        ]);
    }

    public function logout(): void
    {
        $this->logAction('Salir del Sistema', 'AUTH');
        Auth::logout();
        $this->redirect('/login');
    }
}