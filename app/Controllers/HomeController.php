<?php

namespace App\Controllers;

use Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->logAction('Acceso home', 'NAV');
        $view = $this->resolveTemplate('public', 'home/index');

        $this->render($view, [
            'title' => (string) ($_ENV['SITE_TITLE'] ?? 'Custom MVC Template'),
        ]);
    }
}
