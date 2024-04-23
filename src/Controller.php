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

            default:

                include "Views/$view.php";
        }

    }

}