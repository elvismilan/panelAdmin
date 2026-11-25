<?php

namespace App\Controllers;

use App\Models\BlogsModel;
use App\Models\ContenidosModel;
use App\Models\ServicioModel;
use App\Models\SliderModel;
use App\Models\TestimonioModel;
use PanelAdmin\Core\Controller;
use PanelAdmin\Core\Url;
use Throwable;

class HomeController extends Controller
{
    /** ID del registro en `contenidos` que alimenta el hero estatico (respaldo sin slides). */
    private const HERO_CONTENT_ID = 14;

    public function index(): void
    {
        try {
            $slides = (new SliderModel())->getActiveForHome();
        } catch (Throwable $e) {
            error_log('HomeController slider error: ' . $e->getMessage());
            $slides = [];
        }

        try {
            $heroContent = (new ContenidosModel())->findActiveById(self::HERO_CONTENT_ID);
        } catch (Throwable $e) {
            error_log('HomeController hero content error: ' . $e->getMessage());
            $heroContent = null;
        }

        try {
            $servicios = (new ServicioModel())->getActiveForHome();
        } catch (Throwable $e) {
            error_log('HomeController servicios error: ' . $e->getMessage());
            $servicios = [];
        }

        try {
            $posts = (new BlogsModel())->getRecentPublished(3);
        } catch (Throwable $e) {
            error_log('HomeController posts error: ' . $e->getMessage());
            $posts = [];
        }

        try {
            // getPublished() ya devuelve [] si falla la consulta; esto cubre la conexion.
            $testimonios = (new TestimonioModel())->getPublished();
        } catch (Throwable $e) {
            error_log('HomeController testimonios error: ' . $e->getMessage());
            $testimonios = [];
        }

        $siteTitle = (string) ($_ENV['SITE_TITLE'] ?? 'Mi Sitio');
        $metaDescription = $siteTitle . ': sitio web con blog, servicios y contacto.';

        $heroLinkRaw = trim((string) ($heroContent['link'] ?? ''));
        $heroLink = match (true) {
            $heroLinkRaw === '' => Url::to('/contacto'),
            str_starts_with($heroLinkRaw, 'http') => $heroLinkRaw,
            default => Url::to('/' . ltrim($heroLinkRaw, '/')),
        };

        $this->renderPublicModule('home/index', [
            'title' => $siteTitle . '',
            'metaDescription' => $metaDescription,
            'canonical' => Url::to('/'),
            'ogType' => 'website',
            'ogImage' => Url::to('/assets/public-theme/img/favicon.png'),
            'slides' => $slides,
            'heroEyebrow' => trim((string) ($heroContent['subtitle'] ?? '')),
            'heroTitulo' => trim((string) ($heroContent['titulo'] ?? '')) !== '' ? trim((string) $heroContent['titulo']) : $siteTitle,
            'heroContenidoHtml' => trim((string) ($heroContent['contenido'] ?? '')),
            'heroLink' => $heroLink,
            'servicios' => $servicios,
            'posts' => $posts,
            'testimonios' => $testimonios,
            'pageAssets' => [
                'css' => [
                    Url::to('/assets/public-theme/css/style.css'),
                ],
                'js' => [
                    Url::to('/assets/public-theme/js/main.js'),
                    Url::to('/assets/public-theme/js/mega-menu.js'),
                    Url::to('/assets/public-theme/js/nav-scroll.js'),
                    Url::to('/assets/public-theme/js/hero-slider.js'),
                    Url::to('/assets/public-theme/js/testimonios-carousel.js'),
                ],
            ],
        ]);
    }
}
