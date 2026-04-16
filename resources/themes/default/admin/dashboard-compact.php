<?php
$themeDir = dirname(__DIR__);
$adminHeader = $themeDir . '/header-admin.php';
$adminFooter = $themeDir . '/footer-admin.php';
$sharedDir = $themeDir . '/shared';
$sharedHeader = $sharedDir . '/header.php';
$sharedFooter = $sharedDir . '/footer.php';
$dashboardView = dirname(__DIR__, 4) . '/app/Views/dashboard/index.php';
$hasAdminLayout = is_file($adminHeader) && is_file($adminFooter);
?>
<?php if ($hasAdminLayout): ?>
    <?php include $adminHeader; ?>
<?php elseif (is_file($sharedHeader)): ?>
    <?php include $sharedHeader; ?>
<?php endif; ?>
<?php if (is_file($dashboardView)): ?>
    <?php include $dashboardView; ?>
<?php endif; ?>
<?php if ($hasAdminLayout): ?>
    <?php include $adminFooter; ?>
<?php elseif (is_file($sharedFooter)): ?>
    <?php include $sharedFooter; ?>
<?php endif; ?>
