<?php 

namespace Core;

class Controller {

    public function model($model) {
        $modelClass = 'App\\Models\\' . $model;
        return new $modelClass();
    }

    public function view($view, $data = []) {
        \Core\View::render($view, $data);
    }
}
