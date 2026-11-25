<?php
/**
 * Detalle publico de un servicio (modulo Servicios). Incluido por
 * app/Views/shared/layout.php (moduleView = 'servicio-publico/detalle').
 *
 * Ruta: /servicio/{slug} (ver ServicioPublicController::detalle). Page-hero
 * con el titulo del servicio (h1). Body a 2 columnas: texto a la izquierda
 * (subtitulo, titulo, contenido), imagen a la derecha, mismo patron
 * .hero/.hero-copy/.hero-photo que Investigacion.
 */
use PanelAdmin\Core\Helpers\Img;
use PanelAdmin\Core\Url;

$heading = (string) ($heading ?? '');
$subtitulo = (string) ($subtitulo ?? '');
$contentHtml = (string) ($contentHtml ?? '');
$contentImage = (string) ($contentImage ?? '');
?>
<section class="page-hero">
    <div class="container container--narrow">
        <h1><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h1>
        <nav class="breadcrumbs" aria-label="Migas de pan">
            <a href="<?= htmlspecialchars(Url::to('/'), ENT_QUOTES, 'UTF-8') ?>">Inicio</a>
            <span>/</span>
            <span><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
    </div>
</section>

<section class="hero">
    <div class="container">
        <div class="hero-copy">
            <?php if ($subtitulo !== ''): ?>
                <span class="eyebrow"><?= htmlspecialchars($subtitulo, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <h2><?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?></h2>

            <?php if ($contentHtml !== ''): ?>
                <div class="blog-detail-content"><?= $contentHtml ?></div>
            <?php else: ?>
                <p style="color:var(--gray-500);">Muy pronto compartiremos más información sobre este servicio.</p>
            <?php endif; ?>

            <div class="hero-actions">
                <a href="<?= htmlspecialchars(Url::to('/contacto'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn--primary">Contáctanos</a>
                <a href="<?= htmlspecialchars(Url::to('/'), ENT_QUOTES, 'UTF-8') ?>" class="follow-link">← Volver al inicio</a>
            </div>
        </div>

        <?php if ($contentImage !== ''): ?>
            <div class="hero-photo">
                <?= Img::tag($contentImage, $heading, [], 'priority') ?>
            </div>
        <?php endif; ?>
    </div>
</section>
