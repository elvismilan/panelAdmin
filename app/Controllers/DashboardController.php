<?php

namespace App\Controllers;

use Core\Auth;
use Core\Controller;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->logAction('Acceso dashboard', 'NAV');

        $user = Auth::user();
        $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'user' => $user,
        ]);
    }
}