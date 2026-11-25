<?php
/**
 * Layout del admin con theme-one (public/assets/theme-one). El core busca las
 * plantillas del admin primero en resources/themes/admin/ del proyecto (layout,
 * login/*, errors/*), asi que estas reemplazan a las del core. Las pantallas
 * (Usuarios, Grupos, Dashboard...) siguen siendo las del core: header-admin.php y
 * footer-admin.php cargan su CSS y JS (public/assets/paneladmin, de assets:publish).
 *
 * Recibe del core: $moduleView, $theme (ThemeResolver), $pageAssets, $csrfField,
 * $flashes, $adminMenu y los datos de la pantalla.
 */
$moduleView = ltrim((string) ($moduleView ?? ''), '/');
$moduleFile = ($moduleView !== '' && isset($theme) && $theme instanceof \PanelAdmin\Core\ThemeResolver)
    ? $theme->module($moduleView)
    : null;
?>
<?php include __DIR__ . '/header-admin.php'; ?>
<?php if ($moduleFile !== null): ?>
    <?php include $moduleFile; ?>
<?php else: ?>
    <div class="container-fluid"><p>Vista no encontrada.</p></div>
<?php endif; ?>
<?php include __DIR__ . '/footer-admin.php'; ?>
