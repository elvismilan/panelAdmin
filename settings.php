<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$url;

if ($_SERVER['SERVER_NAME'] == "localhost") {
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https' ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://' . $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']);
    $paths = explode("/", $path);
    $url = $protocol . "/" . $paths[1];
} else {
    $protocol = isset($_SERVER["HTTPS"]) ? 'https://' . $_SERVER['HTTP_HOST'] : 'http://' . $_SERVER['HTTP_HOST'];
    $url = $protocol;
}

return array(
    'url' => $url,
    'db_type' => 'mysql',
    'db_host' => 'localhost',
    'db_name' => 'betomotors',
    'db_user' => 'root',
    'db_pass' => 'root'
);