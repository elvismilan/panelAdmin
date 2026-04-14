<?php
$themeDir = dirname(__DIR__);
$adminHeader = $themeDir . '/header-admin.php';
$adminFooter = $themeDir . '/footer-admin.php';
$sharedDir = $themeDir . '/shared';
$sharedHeader = $sharedDir . '/header.php';
$sharedFooter = $sharedDir . '/footer.php';
$moduleView = ltrim((string) ($moduleView ?? ''), '/');
$appView = dirname(__DIR__, 4) . '/app/Views/' . $moduleView . '.php';
$hasAdminLayout = is_file($adminHeader) && is_file($adminFooter);
?>
<?php if ($hasAdminLayout): ?>
    <?php include $adminHeader; ?>
<?php elseif (is_file($sharedHeader)): ?>
    <?php include $sharedHeader; ?>
<?php endif; ?>
<?php if ($moduleView !== '' && is_file($appView)): ?>
    <?php include $appView; ?>
<?php else: ?>
    <div class="container-fluid"><p>Vista no encontrada.</p></div>
<?php endif; ?>
<?php if ($hasAdminLayout): ?>
    <?php include $adminFooter; ?>
<?php elseif (is_file($sharedFooter)): ?>
    <?php include $sharedFooter; ?>
<?php endif; ?>
