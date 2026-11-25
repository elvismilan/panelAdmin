<?php

use App\Controllers\BlogController;
use App\Controllers\BlogsController;
use App\Controllers\ContactoController;
use App\Controllers\ContenidosController;
use App\Controllers\GaleriaController;
use App\Controllers\HomeController;
use App\Controllers\RedesSocialesController;
use App\Controllers\SeoController;
use App\Controllers\ServicioPublicController;
use App\Controllers\ServicioController;
use App\Controllers\SliderController;
use App\Controllers\TestimonioController;

// El admin (login, Dashboard, Usuarios, Personas, Grupos, Modulos, Tareas, Bitacora,
// Parametros y Notificaciones) lo sirve el core con sus pantallas: mejoran con
// `composer update`. Aqui solo van las rutas del proyecto.
$router->get('/', HomeController::class, 'index');

// Modulos CMS (Website)
$router->crud('/blogs', BlogsController::class);
$router->post('/blogs/categorias/agregar', BlogsController::class, 'guardarCategoria');
$router->post('/blogs/categorias/{id}/eliminar', BlogsController::class, 'borrarCategoria');
$router->crud('/contenidos', ContenidosController::class);
$router->crud('/redes', RedesSocialesController::class);
$router->crud('/slider', SliderController::class);
$router->post('/slider/{id}/ordenar', SliderController::class, 'ordenar');
$router->crud('/galerias', GaleriaController::class);
$router->crud('/servicios', ServicioController::class);
$router->crud('/testimonios', TestimonioController::class);
$router->post('/testimonios/{id}/ordenar', TestimonioController::class, 'ordenar');
$router->get('/galerias/{id}/fotos', GaleriaController::class, 'fotos');
$router->post('/galerias/{id}/fotos/guardar', GaleriaController::class, 'fotosGuardar');
$router->post('/galerias/{id}/fotos/{fotoId}/borrar', GaleriaController::class, 'fotosBorrar');

// Blog (sitio publico)
$router->get('/blog', BlogController::class, 'index');
$router->get('/blog/{slug}', BlogController::class, 'detalle');

// Contacto (sitio publico)
$router->get('/contacto', ContactoController::class, 'index');
$router->post('/contacto/enviar', ContactoController::class, 'enviar');

// Servicio (sitio publico)
$router->get('/servicio/{slug}', ServicioPublicController::class, 'detalle');

// SEO (sitio publico)
$router->get('/sitemap.xml', SeoController::class, 'sitemap');
$router->get('/robots.txt', SeoController::class, 'robots');
