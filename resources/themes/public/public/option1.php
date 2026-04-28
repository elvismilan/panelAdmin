<?php
$sharedDir = dirname(__DIR__) . '/shared';
$sharedHeader = $sharedDir . '/header.php';
$sharedFooter = $sharedDir . '/footer.php';
$useStandaloneLayout = !is_file($sharedHeader) && !is_file($sharedFooter);
$pageAssets = $pageAssets ?? ['css' => [], 'js' => []];
$cssAssets = is_array($pageAssets['css'] ?? null) ? $pageAssets['css'] : [];
$jsAssets = is_array($pageAssets['js'] ?? null) ? $pageAssets['js'] : [];
?>
<?php if ($useStandaloneLayout): ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Sitio publico', ENT_QUOTES, 'UTF-8') ?></title>
    <?php foreach ($cssAssets as $cssFile): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($cssFile, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
</head>
<body>
<?php endif; ?>
<?php if (is_file($sharedHeader)) { include $sharedHeader; } ?>
<section>
    <h1><?= htmlspecialchars($title ?? 'Sitio publico', ENT_QUOTES, 'UTF-8') ?></h1>
    <p>Template publico - opcion 1.</p>
    <p><a href="<?= htmlspecialchars(\Core\Url::to('/login'), ENT_QUOTES, 'UTF-8') ?>">Acceso administrador</a></p>
</section>
<?php if (is_file($sharedFooter)) { include $sharedFooter; } ?>
<?php if ($useStandaloneLayout): ?>
    <?php foreach ($jsAssets as $jsFile): ?>
        <script src="<?= htmlspecialchars($jsFile, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endforeach; ?>
</body>
</html>
<?php endif; ?>
