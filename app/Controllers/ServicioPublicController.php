<?php

namespace App\Controllers;

use App\Models\ServicioModel;
use PanelAdmin\Core\Controller;
use PanelAdmin\Core\Helpers\ImageHelper;
use PanelAdmin\Core\Url;
use Throwable;

/** Detalle publico de un servicio (modulo Servicios): /servicio/{slug}. */
class ServicioPublicController extends Controller
{
    public function detalle(string $slug): void
    {
        $model = new ServicioModel();
        $servicio = $model->findPublicBySlug($slug);

        if (!is_array($servicio)) {
            http_response_code(404);
            $this->view->render('resources:themes/admin/errors/not-found', [
                'title' => 'Servicio no encontrado',
                'code' => 404,
                'heading' => 'No encontramos este servicio',
                'message' => 'El servicio solicitado no existe o ya no esta disponible.',
                'homeUrl' => Url::to('/'),
            ]);
            return;
        }

        try {
            $model->incrementViews((string) ($servicio['id'] ?? ''));
        } catch (Throwable $e) {
            error_log('ServicioPublicController incrementViews error: ' . $e->getMessage());
        }

        $siteTitle = (string) ($_ENV['SITE_TITLE'] ?? 'Mi Sitio');
        $titulo = trim((string) ($servicio['titulo'] ?? ''));
        $subtitulo = trim((string) ($servicio['subtitulo'] ?? ''));
        $contenidoHtml = trim((string) ($servicio['contenido'] ?? ''));
        $imagenRaw = trim((string) ($servicio['imagen'] ?? ''));

        $plainText = $subtitulo !== '' ? $subtitulo : trim(strip_tags($contenidoHtml));
        $metaDescription = $plainText !== ''
            ? (mb_strlen($plainText) > 160 ? rtrim(mb_substr($plainText, 0, 160)) . '…' : $plainText)
            : $titulo . ' | ' . $siteTitle;

        $this->renderPublicModule('servicio-publico/detalle', [
            'title' => $titulo . ' | ' . $siteTitle,
            'metaDescription' => $metaDescription,
            'canonical' => Url::to('/servicio/' . $slug),
            'activeNav' => 'inicio',
            'heading' => $titulo,
            'subtitulo' => $subtitulo,
            'contentHtml' => $contenidoHtml,
            'contentImage' => $imagenRaw !== '' ? Url::to('/' . ImageHelper::original($imagenRaw, 'servicios')) : '',
            'pageAssets' => [
                'css' => [Url::to('/assets/public-theme/css/style.css')],
                'js' => [
                    Url::to('/assets/public-theme/js/main.js'),
                    Url::to('/assets/public-theme/js/mega-menu.js'),
                    Url::to('/assets/public-theme/js/nav-scroll.js'),
                ],
            ],
        ]);
    }
}
