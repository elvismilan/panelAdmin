<?php

namespace App\Controllers;

use App\Controller;

class AuthController extends Controller
{
    public function index() {

        $this->render('Dashboard/index');
    }
}