<?php
/**
 * Footer compartido del sitio publico.
 * Incluir desde cada vista de pagina publica, justo antes de cerrar <main>/<body>.
 *
 * La pagina que lo incluye debe encolar 'public-theme/js/main.js' en $pageAssets
 * (boton volver arriba + menu movil comparten ese script).
 *
 * Variable opcional: $footerRedes (array de redes_sociales activas). Si no se
 * pasa, se consultan aqui mismo para que el footer funcione en cualquier vista
 * sin depender de que el controller la provea.
 */
use App\Models\ContenidosModel;
use App\Models\RedesSocialesModel;
use PanelAdmin\Core\Helpers\SocialIconHelper;
use PanelAdmin\Core\Url;

if (!isset($footerRedes) || !is_array($footerRedes)) {
    try {
        $footerRedes = (new RedesSocialesModel())->getActiveForHome();
    } catch (\Throwable) {
        $footerRedes = [];
    }
}

// Contenido editable desde el admin (Footer Widget 1 = marca, Footer Widget 3 = contacto).
// Si el contenido no existe o esta inactivo, se usan los valores fijos de respaldo.
try {
    $contenidosModel = new ContenidosModel();
    $footerBrandContent = $contenidosModel->findActiveById(1);
    $footerContactContent = $contenidosModel->findActiveById(3);
} catch (\Throwable) {
    $footerBrandContent = null;
    $footerContactContent = null;
}

$siteTitle = (string) ($_ENV['SITE_TITLE'] ?? 'Mi Sitio');
$contactHeading = $footerContactContent !== null && trim((string) $footerContactContent['titulo']) !== ''
    ? trim((string) $footerContactContent['titulo'])
    : 'Hablemos';
$contactEmail = $footerContactContent !== null && trim((string) ($footerContactContent['link'] ?? '')) !== ''
    ? trim((string) $footerContactContent['link'])
    : trim((string) ($_ENV['SITE_CONTACT_EMAIL'] ?? ''));
$contactPhone = $footerContactContent !== null && trim((string) ($footerContactContent['subtitle'] ?? '')) !== ''
    ? trim((string) $footerContactContent['subtitle'])
    : trim((string) ($_ENV['SITE_CONTACT_PHONE'] ?? ''));
$contactCtaHtml = $footerContactContent !== null ? trim((string) $footerContactContent['contenido']) : '';

$footerNav = [
    ['label' => 'Blog', 'url' => '/blog'],
];
?>
<footer class="site-footer">
    <div class="footer-glow" aria-hidden="true"></div>

    <div class="footer-top">
        <div class="container footer-grid">
            <div class="footer-col footer-col--brand">
                <a href="<?= htmlspecialchars(Url::to('/'), ENT_QUOTES, 'UTF-8') ?>" class="footer-logo-link" aria-label="<?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8') ?>">
                    <img src="<?= htmlspecialchars(Url::to('/assets/public-theme/img/logo-white.svg'), ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8') ?>"
                         class="footer-logo">
                </a>
                <?php if ($footerBrandContent !== null && trim((string) $footerBrandContent['contenido']) !== ''): ?>
                    <?= $footerBrandContent['contenido'] ?>
                <?php else: ?>
                    <p class="footer-tagline"><?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <?php if ($footerRedes !== []): ?>
                    <ul class="footer-social" aria-label="Redes sociales">
                        <?php foreach ($footerRedes as $red): ?>
                            <?php
                            $link = trim((string) ($red['res_link'] ?? ''));
                            $titulo = trim((string) ($red['res_titulo'] ?? ''));
                            if ($link === '') {
                                continue;
                            }
                            $icon = SocialIconHelper::resolve((string) ($red['res_icon'] ?? ''), $titulo);
                            ?>
                            <li>
                                <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>"
                                   target="_blank" rel="noopener noreferrer"
                                   aria-label="<?= htmlspecialchars($titulo !== '' ? $titulo : 'Red social', ENT_QUOTES, 'UTF-8') ?>">
                                    <svg viewBox="<?= htmlspecialchars($icon['viewBox'], ENT_QUOTES, 'UTF-8') ?>" fill="currentColor" aria-hidden="true">
                                        <path d="<?= htmlspecialchars($icon['d'], ENT_QUOTES, 'UTF-8') ?>"/>
                                    </svg>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="footer-col">
                <h4 class="footer-heading">Menu</h4>
                <ul class="footer-nav">
                    <?php foreach ($footerNav as $item): ?>
                        <li><a href="<?= htmlspecialchars(Url::to($item['url']), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="footer-col footer-col--contact">
                <h4 class="footer-heading"><?= htmlspecialchars($contactHeading, ENT_QUOTES, 'UTF-8') ?></h4>
                <ul class="footer-contact">
                    <?php if ($contactEmail !== ''): ?>
                        <li>
                            <span class="footer-contact-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            </span>
                            <a href="mailto:<?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if ($contactPhone !== ''): ?>
                        <li>
                            <span class="footer-contact-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.7.6a2 2 0 0 1 1.7 2Z"/></svg>
                            </span>
                            <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $contactPhone), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8') ?></a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="footer-cta">
                    <?php if ($contactCtaHtml !== ''): ?>
                        <?= $contactCtaHtml ?>
                    <?php else: ?>
                        <p>¿Listo para dar el primer paso? Agenda tu primera sesión.</p>
                    <?php endif; ?>
                    <a href="<?= htmlspecialchars(Url::to('/#contacto'), ENT_QUOTES, 'UTF-8') ?>" class="footer-cta-btn">
                        <span class="footer-cta-btn-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                        </span>
                        <span>Agendar sesión</span>
                        <svg class="footer-cta-btn-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <span><strong><?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8') ?></strong> &copy; <?= date('Y') ?>. Todos los derechos reservados.</span>
            <span class="footer-bottom-tag">Desarrollado por <a href="https://elvismilan.com/" target="_blank" rel="noopener"><strong>Elvis Milan</strong></a></span>
        </div>
    </div>
</footer>

<button class="back-to-top" id="backToTop" aria-label="Volver arriba">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 15 7-7 7 7"/></svg>
</button>
