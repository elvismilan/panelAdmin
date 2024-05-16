<?php

namespace App;

class Controller
{

    protected function render($view, $type = 'admin', $data = []) {
    
        extract($data);

        switch($type) {

            case 'web' : 

                break;

            case 'admin' : 

                require_once 'Views/layouts/partials/header.php';
                include "Views/$view.php";
                require_once 'Views/layouts/partials/footer.php';
                break;

            default:

                include "Views/$view.php";

        }

    }

}