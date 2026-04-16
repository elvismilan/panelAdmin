<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Mostrar errores solo en modo debug (APP_DEBUG=true en .env)
$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
error_reporting($debug ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');

// -------------------------------------------------------------------------
// Headers de seguridad HTTP
// -------------------------------------------------------------------------

// Evita que el navegador infiera el MIME type del contenido
header('X-Content-Type-Options: nosniff');

// Impide que la pagina sea embebida en iframes de otros dominios (clickjacking)
header('X-Frame-Options: SAMEORIGIN');

// Controla cuanta informacion de referencia se envia al navegar
header('Referrer-Policy: strict-origin-when-cross-origin');

// Restringe permisos de APIs del navegador innecesarias
header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');

// Content Security Policy
// - default-src 'self'                         solo recursos del mismo dominio por defecto
// - script-src  'self' 'unsafe-inline'         permite scripts inline del tema (Bootstrap/jQuery)
// - style-src   'self' 'unsafe-inline' fonts.googleapis.com  permite estilos inline + Google Fonts CSS
// - img-src     'self' data:                   permite imagenes locales y base64
// - font-src    'self' data: fonts.gstatic.com permite fuentes locales, embebidas y Google Fonts archivos
// - form-action 'self'                         los formularios solo envian datos al mismo dominio
// - frame-ancestors 'none'                     ninguna pagina puede embeber este panel en un iframe
// - base-uri    'self'                         previene inyeccion via etiqueta <base>
// Nota: elimina 'unsafe-inline' en script-src si el tema no usa scripts inline.
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data:; font-src 'self' data: https://fonts.gstatic.com; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");

// Elimina el header que expone la version de PHP
header_remove('X-Powered-By');

use Core\App;
use Core\Router;

$app = new App(new Router());

$router = $app->getRouter();
require_once __DIR__ . '/../routes/web.php';

$app->run();