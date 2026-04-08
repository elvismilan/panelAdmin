<?php

namespace App\Controllers;

use App\Controller;

class DashboardController extends Controller {

    public function index() {

        $this->render('Dashboard/index');

    }

    public function sample() {

        $this->render('Dashboard/index');

    }

}