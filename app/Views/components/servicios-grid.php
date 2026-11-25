<?php
/**
 * Grilla de servicios activos: icono, titulo y subtitulo, con enlace a su
 * detalle (/servicio/{slug}). Reutilizado en Home. Ver .toc-grid/.toc-card en
 * public-theme/css/style.css.
 *
 * Variables a definir antes de incluir:
 *   $servicios        array   Servicios activos (id, titulo, subtitulo, slug, icon).
 *   $sgTitulo         string  Titulo de la seccion (h2).
 *   $sgHeadingId      string  ID del <h2> para aria-labelledby.  Default: 'servicios-grid-title'
 *   $sgMuted          bool    Aplica fondo .section--muted.      Default: false
 */
use PanelAdmin\Core\Helpers\ImageHelper;
use PanelAdmin\Core\Helpers\Img;
use PanelAdmin\Core\Url;

$servicios = is_array($servicios ?? null) ? $servicios : [];
$sgTitulo = $sgTitulo ?? 'Especialidades';
$sgHeadingId = $sgHeadingId ?? 'servicios-grid-title';
$sgMuted = $sgMuted ?? false;
?>
<?php if ($servicios !== []): ?>
    <section class="section<?= $sgMuted ? ' section--muted' : '' ?>" aria-labelledby="<?= htmlspecialchars($sgHeadingId, ENT_QUOTES, 'UTF-8') ?>">
        <div class="container">
            <div class="section-title-wrap">
                <h2 id="<?= htmlspecialchars($sgHeadingId, ENT_QUOTES, 'UTF-8') ?>" class="section-title"><?= htmlspecialchars($sgTitulo, ENT_QUOTES, 'UTF-8') ?></h2>
                <div class="divider"></div>
            </div>

            <div class="toc-grid">
                <?php foreach ($servicios as $servicio): ?>
                    <?php
                    $iconRaw = trim((string) ($servicio['icon'] ?? ''));
                    $iconPath = $iconRaw !== '' ? ImageHelper::original($iconRaw, 'servicios') : '';
                    $servicioSlug = trim((string) ($servicio['slug'] ?? ''));
                    $servicioSubtitulo = trim((string) ($servicio['subtitulo'] ?? ''));
                    ?>
                    <article class="toc-card">
                        <?php if ($iconPath !== ''): ?>
                            <div class="toc-icon" aria-hidden="true">
                                <?= Img::tag('/' . $iconPath) ?>
                            </div>
                        <?php endif; ?>
                        <h3><?= htmlspecialchars((string) ($servicio['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                        <?php if ($servicioSubtitulo !== ''): ?>
                            <p><?= htmlspecialchars($servicioSubtitulo, ENT_QUOTES, 'UTF-8') ?></p>
                        <?php endif; ?>
                        <?php if ($servicioSlug !== ''): ?>
                            <p><a href="<?= htmlspecialchars(Url::to('/servicio/' . $servicioSlug), ENT_QUOTES, 'UTF-8') ?>" class="read-more">Leer más →</a></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
