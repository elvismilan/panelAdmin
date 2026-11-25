<?php
/**
 * Header compartido del sitio publico: topbar fijo, transparente en reposo
 * y con efecto glass/blur al hacer scroll. Hamburguesa + "Menu" a la
 * izquierda, logo centrado, boton "Agendar" a la derecha. La hamburguesa
 * abre un megamenu de pantalla completa (enlaces a la izquierda, contacto +
 * redes a la derecha); mientras esta abierto el logo y el boton Agendar se
 * ocultan y solo queda visible el toggle (ahora con icono X y texto Cerrar)
 * en la misma posicion.
 *
 * Incluir desde cada vista de pagina publica, al inicio de <body>.
 * Variable opcional: $activeNav (string) para marcar el item activo del menu
 * (valores: 'inicio', 'blog', 'contacto').
 *
 * La pagina que lo incluye debe encolar 'public-theme/js/mega-menu.js'.
 */
use App\Models\ContenidosModel;
use App\Models\RedesSocialesModel;
use PanelAdmin\Core\Helpers\SocialIconHelper;
use PanelAdmin\Core\Url;

if (!isset($headerRedes) || !is_array($headerRedes)) {
    try {
        $headerRedes = (new RedesSocialesModel())->getActiveForHome();
    } catch (\Throwable) {
        $headerRedes = [];
    }
}

// Mismo contenido editable que usa el footer (Footer Widget 3 = contacto):
// link = email, subtitle = telefono. Si no existe o esta inactivo, se cae a .env.
try {
    $headerContactContent = (new ContenidosModel())->findActiveById(3);
} catch (\Throwable) {
    $headerContactContent = null;
}

$siteTitle = (string) ($_ENV['SITE_TITLE'] ?? 'Mi Sitio');
$activeNav = (string) ($activeNav ?? '');
$contactEmail = $headerContactContent !== null && trim((string) ($headerContactContent['link'] ?? '')) !== ''
    ? trim((string) $headerContactContent['link'])
    : trim((string) ($_ENV['SITE_CONTACT_EMAIL'] ?? ''));
$contactPhone = $headerContactContent !== null && trim((string) ($headerContactContent['subtitle'] ?? '')) !== ''
    ? trim((string) $headerContactContent['subtitle'])
    : trim((string) ($_ENV['SITE_CONTACT_PHONE'] ?? ''));

$mainNav = [
    ['key' => 'inicio', 'label' => 'Inicio', 'url' => '/'],
    ['key' => 'blog', 'label' => 'Blog', 'url' => '/blog'],
    ['key' => 'contacto', 'label' => 'Contacto', 'url' => '/contacto'],
];

$renderSocialRow = static function (array $redes): void {
    if ($redes === []) {
        return;
    }
    ?>
    <ul class="social-row" aria-label="Redes sociales">
        <?php foreach ($redes as $red): ?>
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
    <?php
};
?>
<header class="site-header">
    <div class="topbar">
        <div class="container">
            <button type="button" class="menu-toggle" id="menuToggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="megaMenu">
                <span class="menu-toggle-bars" aria-hidden="true"><span></span><span></span><span></span></span>
                <span class="menu-toggle-label">
                    <span class="label-open">Menu</span>
                    <span class="label-close">Cerrar</span>
                </span>
            </button>

            <div class="brand">
                <a href="<?= htmlspecialchars(Url::to('/'), ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8') ?>">
                    <img src="<?= htmlspecialchars(Url::to('/assets/public-theme/img/logo-white.svg'), ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8') ?>"
                         class="brand-logo brand-logo--white">
                    <img src="<?= htmlspecialchars(Url::to('/assets/public-theme/img/logo-blue.svg'), ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8') ?>"
                         class="brand-logo brand-logo--blue">
                </a>
            </div>

            <a href="<?= htmlspecialchars(Url::to('/#contacto'), ENT_QUOTES, 'UTF-8') ?>" class="btn-agendar">
                <span class="btn-agendar-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/><path d="M12 14v4M10 16h4"/></svg>
                </span>
                <span>Agendar</span>
            </a>
        </div>
    </div>

    <div class="mega-menu" id="megaMenu" aria-hidden="true">
        <div class="mega-menu-inner">
        <nav class="mega-menu-nav" aria-label="Navegación principal">
            <ul>
                <?php foreach ($mainNav as $item): ?>
                    <?php if ($item['key'] === 'inicio') continue; ?>
                    <li>
                        <a href="<?= htmlspecialchars(Url::to($item['url']), ENT_QUOTES, 'UTF-8') ?>"
                           <?= $activeNav === $item['key'] ? ' aria-current="page"' : '' ?>>
                            <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="mega-menu-aside">
            <div class="mega-menu-aside-block">
                <h4 class="mega-menu-heading">Contacto</h4>
                <?php if ($contactEmail !== ''): ?>
                    <a class="mega-menu-contact-item" href="mailto:<?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?>">
                        <span class="mega-menu-contact-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        </span>
                        <span><?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                <?php endif; ?>
                <?php if ($contactPhone !== ''): ?>
                    <a class="mega-menu-contact-item" href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $contactPhone), ENT_QUOTES, 'UTF-8') ?>">
                        <span class="mega-menu-contact-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.7.6a2 2 0 0 1 1.7 2Z"/></svg>
                        </span>
                        <span><?= htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                <?php endif; ?>
            </div>

            <div class="mega-menu-aside-block">
                <h4 class="mega-menu-heading">Sígueme</h4>
                <?php $renderSocialRow($headerRedes); ?>
            </div>

            <a href="<?= htmlspecialchars(Url::to('/#contacto'), ENT_QUOTES, 'UTF-8') ?>" class="btn-agendar btn-agendar--aside">
                <span class="btn-agendar-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/><path d="M12 14v4M10 16h4"/></svg>
                </span>
                <span>Agendar sesión</span>
            </a>
        </div>
        </div>
    </div>
</header>
