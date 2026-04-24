<?php
$sharedDir = dirname(__DIR__) . '/shared';
$sharedHeader = $sharedDir . '/header.php';
$sharedFooter = $sharedDir . '/footer.php';
$useStandaloneLayout = !is_file($sharedHeader) && !is_file($sharedFooter);
?>
<?php if ($useStandaloneLayout): ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Public', ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
<?php endif; ?>
<?php if (is_file($sharedHeader)) { include $sharedHeader; } ?>
    <h1><?= htmlspecialchars($title ?? 'Public', ENT_QUOTES, 'UTF-8') ?></h1>
    <p>Template publico default - opcion 2.</p>
    <p><a href="<?= htmlspecialchars(\Core\Url::to('/login'), ENT_QUOTES, 'UTF-8') ?>">Ingresar al panel</a></p>
<?php if (is_file($sharedFooter)) { include $sharedFooter; } ?>
<?php if ($useStandaloneLayout): ?>
</body>
</html>
<?php endif; ?>
