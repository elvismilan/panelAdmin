<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Template', ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <h1><?= htmlspecialchars($title ?? 'Template', ENT_QUOTES, 'UTF-8') ?></h1>
    <p>Plantilla base PHP 8 lista para proyectos en hosting compartido.</p>
        <p><a href="<?= htmlspecialchars(\Core\Url::to('/login'), ENT_QUOTES, 'UTF-8') ?>">Ir a login</a></p>
</body>
</html>