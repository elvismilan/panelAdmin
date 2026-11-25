<?php
/**
 * Pagina de Contacto del sitio publico. Incluido por
 * app/Views/shared/layout.php (moduleView = 'contacto/index').
 *
 * Secciones: hero (igual al resto de inner pages), tarjetas de telefono y correo
 * montadas sobre el borde del hero, formulario de contacto
 * (Nombre/Correo/Telefono/Mensaje -> POST /contacto/enviar).
 */
use PanelAdmin\Core\Csrf;
use PanelAdmin\Core\Url;

$contactEmail = (string) ($contactEmail ?? '');
$contactPhone = (string) ($contactPhone ?? '');
$recaptchaEnabled = (bool) ($recaptchaEnabled ?? false);
$recaptchaSiteKey = (string) ($recaptchaSiteKey ?? '');
?>
<section class="page-hero">
    <div class="container container--narrow">
        <h1>Contacto</h1>
        <nav class="breadcrumbs" aria-label="Migas de pan">
            <a href="<?= htmlspecialchars(Url::to('/'), ENT_QUOTES, 'UTF-8') ?>">Inicio</a>
            <span>/</span>
            <span>Contacto</span>
        </nav>
    </div>
</section>

<?php if ($contactEmail !== '' || $contactPhone !== ''): ?>
<section class="contact-cards" aria-label="Datos de contacto">
    <div class="container">
        <div class="contact-cards-grid">
            <?php if ($contactPhone !== ''): ?>
                <a class="contact-card" href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $contactPhone), ENT_QUOTES, 'UTF-8') ?>">
                    <span class="contact-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.5 2.7.6a2 2 0 0 1 1.7 2Z"/></svg>
                    </span>
                    <span class="contact-card-text">
                        <span class="contact-card-label">Llámame</span>
                        <span class="contact-card-value"><?= htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8') ?></span>
                    </span>
                </a>
            <?php endif; ?>
            <?php if ($contactEmail !== ''): ?>
                <a class="contact-card" href="mailto:<?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?>">
                    <span class="contact-card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    </span>
                    <span class="contact-card-text">
                        <span class="contact-card-label">Escríbeme</span>
                        <span class="contact-card-value"><?= str_replace('@', '@<wbr>', htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8')) ?></span>
                    </span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container container--form">
        <div class="section-title-wrap">
            <h2 class="section-title">Ponte en contacto</h2>
            <div class="divider"></div>
        </div>
        <p style="text-align:center;color:var(--gray-500);max-width:60ch;margin:0 auto 40px;">
            No dudes en ponerte en contacto conmigo mediante este formulario y te contactaré lo antes posible.
        </p>

        <form class="contact-form contact-form--cols-3" id="contactoForm" novalidate
              data-action="<?= htmlspecialchars(Url::to('/contacto/enviar'), ENT_QUOTES, 'UTF-8') ?>"
              data-csrf="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-grid">
                <div class="field">
                    <label class="sr-only" for="nombre">Nombre completo</label>
                    <input id="nombre" name="nombre" type="text" placeholder="Nombre completo*">
                </div>
                <div class="field">
                    <label class="sr-only" for="email">Correo Eléctronico</label>
                    <input id="email" name="email" type="text" placeholder="Correo Eléctronico">
                </div>
                <div class="field">
                    <label class="sr-only" for="telefono">Teléfono</label>
                    <input id="telefono" name="telefono" type="tel" placeholder="Teléfono*">
                </div>
                <div class="field field--full">
                    <label class="sr-only" for="mensaje">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" placeholder="Mensaje*"></textarea>
                </div>
            </div>
            <div class="form-footer">
                <?php if ($recaptchaEnabled && $recaptchaSiteKey !== ''): ?>
                    <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($recaptchaSiteKey, ENT_QUOTES, 'UTF-8') ?>"></div>
                <?php endif; ?>
                <button type="submit" class="btn btn--primary">Enviar</button>
            </div>
            <p class="form-status" role="status"></p>
        </form>
    </div>
</section>

<?php if ($recaptchaEnabled && $recaptchaSiteKey !== ''): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
