<?php

namespace App;

class Controller
{

    protected function render($view, $type = 'admin', $data = []) {
    
        extract($data);

        switch($type) {

            case 'web' : 

                include "Views/$view.php";
                break;

            case 'dashboard' : 

                require_once 'Resources/layouts/partials/header.php';
                include "Views/$view.php";
                require_once 'Resources/layouts/partials/footer.php';
                break;

            default:

                include "Views/$view.php";
        }

    }

}