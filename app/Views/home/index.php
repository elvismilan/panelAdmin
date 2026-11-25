<?php
/**
 * Contenido de la pagina de Inicio del sitio publico.
 * Incluido por app/Views/shared/layout.php (moduleView = 'home/index').
 *
 * Variables: $slides (slider activos), $servicios (activos), $posts (ultimos
 * publicados), $testimonios (habilitados) y $heroEyebrow/$heroTitulo/
 * $heroContenidoHtml/$heroLink (registro de `contenidos` de la seccion intro).
 *
 * Secciones: hero (slider), intro, servicios, blog reciente, testimonios y un
 * llamado a la pagina de Contacto.
 */
use PanelAdmin\Core\Helpers\ImageHelper;
use PanelAdmin\Core\Helpers\Img;
use PanelAdmin\Core\Url;

$slides = is_array($slides ?? null) ? $slides : [];
$servicios = is_array($servicios ?? null) ? $servicios : [];
$posts = is_array($posts ?? null) ? $posts : [];
$siteTitle = (string) ($_ENV['SITE_TITLE'] ?? 'Mi Sitio');

$mesesEs = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
    7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
$formatFechaEs = static function (string $datetime) use ($mesesEs): string {
    $ts = strtotime($datetime);
    if ($ts === false) {
        return '';
    }
    return $mesesEs[(int) date('n', $ts)] . ' ' . date('Y', $ts);
};

$heroSlideImages = [];
$heroQuotes = [];
?>
<?php if ($slides !== []): ?>
    <?php
    foreach ($slides as $slide) {
        $img = trim((string) ($slide['imagen'] ?? ''));
        $imgPath = $img !== '' ? ImageHelper::original($img, 'slider') : '';
        if ($imgPath === '') {
            continue;
        }
        $heroSlideImages[] = ['path' => $imgPath, 'titulo' => (string) ($slide['titulo'] ?? '')];

        $quote = trim((string) ($slide['description'] ?? ''));
        if ($quote === '') {
            $quote = trim((string) ($slide['subtitle'] ?? ''));
        }
        $heroQuotes[] = $quote;
    }
    ?>
    <?php if ($heroSlideImages !== []): ?>
        <!-- ============================ HERO (portada, slider) ============================ -->
        <section class="hero-banner" aria-label="Presentación">
            <div class="hero-banner-media">
                <?php foreach ($heroSlideImages as $i => $slideImg): ?>
                    <?= Img::tag('/' . $slideImg['path'], $slideImg['titulo'], [
                        'class' => 'hero-banner-img' . ($i === 0 ? ' is-active' : ''),
                        'data-slide-index' => $i,
                    ], $i === 0 ? 'priority' : 'lazy') ?>
                <?php endforeach; ?>
            </div>

            <div class="hero-banner-card">
                <div class="hero-banner-signature">
                    <?php // Unico <h1> de la home cuando hay slider (el intro pasa a <h2>). ?>
                    <h1 class="hero-banner-signature-text">
                        <span><?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8') ?></span>
                    </h1>
                </div>
                <div class="hero-banner-quote">
                    <p class="hero-banner-text" id="heroBannerText"><?= htmlspecialchars($heroQuotes[0] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>

            <?php if (count($heroSlideImages) > 1): ?>
                <div class="hero-banner-dots" role="tablist" aria-label="Presentaciones">
                    <?php foreach ($heroSlideImages as $i => $slideImg): ?>
                        <button type="button"
                                class="hero-banner-dot<?= $i === 0 ? ' is-active' : '' ?>"
                                data-slide-index="<?= $i ?>"
                                aria-label="Presentación <?= $i + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <script>window.__heroQuotes = <?= json_encode($heroQuotes, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;</script>
    <?php endif; ?>
<?php endif; ?>

<!-- ============================ INTRO ============================ -->
<?php
$heroEyebrow = (string) ($heroEyebrow ?? '');
$heroTitulo = (string) ($heroTitulo ?? $siteTitle);
$heroContenidoHtml = (string) ($heroContenidoHtml ?? '');
$heroLink = (string) ($heroLink ?? Url::to('/contacto'));
?>
<section class="hero-static" aria-label="Presentación">
    <div class="hero-static-media" aria-hidden="true"></div>
    <div class="container">
        <div class="hero-static-copy">
            <?php if ($heroEyebrow !== ''): ?>
                <span class="eyebrow"><?= htmlspecialchars($heroEyebrow, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
            <?php if ($slides === [] || $heroSlideImages === []): ?>
                <h1><?= htmlspecialchars($heroTitulo, ENT_QUOTES, 'UTF-8') ?></h1>
            <?php else: ?>
                <h2><?= htmlspecialchars($heroTitulo, ENT_QUOTES, 'UTF-8') ?></h2>
            <?php endif; ?>
            <div class="divider divider--left"></div>
            <?php if ($heroContenidoHtml !== ''): ?>
                <div class="hero-static-text"><?= $heroContenidoHtml ?></div>
            <?php endif; ?>
            <a href="<?= htmlspecialchars($heroLink, ENT_QUOTES, 'UTF-8') ?>" class="btn btn--primary">Leer más</a>
        </div>
    </div>
</section>

<!-- ============================ SERVICIOS ============================ -->
<?php
$sgTitulo = 'Servicios';
$sgHeadingId = 'servicios-title';
$sgMuted = false;
?>
<?php include dirname(__DIR__) . '/components/servicios-grid.php'; ?>

<?php if ($posts !== []): ?>
    <!-- ============================ BLOG ============================ -->
    <section class="section section--muted" aria-labelledby="blog-title">
        <div class="container">
            <div class="section-title-wrap">
                <h2 id="blog-title" class="section-title">Nuestro Blog</h2>
                <div class="divider"></div>
            </div>
            <div class="blog-grid">
                <?php foreach ($posts as $post): ?>
                    <?php
                    $created = trim((string) ($post['created'] ?? ''));
                    $fecha = $created !== '' ? $formatFechaEs($created) : '';
                    $postHref = Url::to('/blog/' . (string) ($post['slug'] ?? ''));
                    $imagenRaw = trim((string) ($post['imagen'] ?? ''));
                    $imagenPath = $imagenRaw !== '' ? ImageHelper::thumb($imagenRaw, 'blogs', '641x430') : '';
                    ?>
                    <article class="blog-card blog-card--with-image blog-card--flat">
                        <?php if ($imagenPath !== ''): ?>
                            <a href="<?= htmlspecialchars($postHref, ENT_QUOTES, 'UTF-8') ?>" class="blog-card-media">
                                <?= Img::tag('/' . $imagenPath, (string) ($post['titulo'] ?? '')) ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($fecha !== ''): ?><span class="blog-date"><?= htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                        <h3><a href="<?= htmlspecialchars($postHref, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) ($post['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a></h3>
                        <p><?= htmlspecialchars((string) ($post['detalle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        <a href="<?= htmlspecialchars($postHref, ENT_QUOTES, 'UTF-8') ?>" class="read-more">Leer más →</a>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="blog-more">
                <a href="<?= htmlspecialchars(Url::to('/blog'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn--primary btn--shine">
                    Ver todos los artículos
                    <svg class="btn-arrow" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- ============================ TESTIMONIOS ============================ -->
<?php
$testimoniosTitulo = 'Lo que dicen nuestros clientes';
$testimoniosEyebrow = 'Testimonios';
$testimoniosId = 'testimonios';
?>
<?php include dirname(__DIR__) . '/components/testimonios.php'; ?>

<!-- ============================ CONTACTO ============================ -->
<section class="section" id="contacto" aria-labelledby="contacto-title">
    <div class="container">
        <div class="section-title-wrap">
            <h2 id="contacto-title" class="section-title">Contáctanos</h2>
            <div class="divider"></div>
        </div>
        <p class="text-center">
            <a href="<?= htmlspecialchars(Url::to('/contacto'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn--primary">Escríbenos</a>
        </p>
    </div>
</section>
