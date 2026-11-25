<?php
/**
 * Layout compartido del sitio publico: doctype/head/body, header y footer
 * fijos, y el contenido especifico de cada pagina inyectado en medio.
 * Misma logica que resources/themes/admin/layout.php para el panel.
 *
 * Variables esperadas: $moduleView (ruta relativa a app/Views, ej. 'home/index'),
 * $title, $metaDescription (opcional), $pageAssets (['css' => [], 'js' => []]).
 *
 * SEO (todas opcionales, con fallbacks razonables):
 * $canonical (URL absoluta), $robots (default 'index, follow'),
 * $ogType ('website'|'article', default 'website'), $ogImage (URL absoluta),
 * $jsonLd (array de arrays, cada uno se serializa como <script type="application/ld+json">).
 */
use PanelAdmin\Core\Url;

$moduleView = ltrim((string) ($moduleView ?? ''), '/');
$appView = __DIR__ . '/../' . $moduleView . '.php';

$pageAssets = is_array($pageAssets ?? null) ? $pageAssets : ['css' => [], 'js' => []];
$cssAssets = is_array($pageAssets['css'] ?? null) ? $pageAssets['css'] : [];
$jsAssets = is_array($pageAssets['js'] ?? null) ? $pageAssets['js'] : [];

$siteTitle = (string) ($_ENV['SITE_TITLE'] ?? 'Mi Sitio');
$pageTitle = trim((string) ($title ?? '')) !== '' ? (string) $title : $siteTitle;
$metaDescription = trim((string) ($metaDescription ?? ''));
$canonical = trim((string) ($canonical ?? Url::to($_SERVER['REQUEST_URI'] ?? '/')));
$robots = trim((string) ($robots ?? '')) !== '' ? (string) $robots : 'index, follow';
$ogType = trim((string) ($ogType ?? '')) !== '' ? (string) $ogType : 'website';
$ogImage = trim((string) ($ogImage ?? ''));
if ($ogImage === '') {
    $ogImage = Url::to('/assets/public-theme/img/favicon.png');
}
$jsonLd = is_array($jsonLd ?? null) ? $jsonLd : [];

// Google Analytics 4: solo se carga si GA_MEASUREMENT_ID tiene formato valido (G-XXXX).
$gaMeasurementId = trim((string) ($_ENV['GA_MEASUREMENT_ID'] ?? ''));
if (preg_match('/^G-[A-Z0-9]{4,20}$/', $gaMeasurementId) !== 1) {
    $gaMeasurementId = '';
}
?>
<!doctype html>
<html lang="es">
<head>
    <?php if ($gaMeasurementId !== ''): ?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($gaMeasurementId, ENT_QUOTES, 'UTF-8') ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', '<?= htmlspecialchars($gaMeasurementId, ENT_QUOTES, 'UTF-8') ?>');
    </script>
    <?php endif; ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <?php if ($metaDescription !== ''): ?>
        <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <meta name="robots" content="<?= htmlspecialchars($robots, ENT_QUOTES, 'UTF-8') ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">

    <meta property="og:site_name" content="<?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:type" content="<?= htmlspecialchars($ogType, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:locale" content="es_ES">
    <?php if ($metaDescription !== ''): ?>
        <meta property="og:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php if ($ogImage !== ''): ?>
        <meta property="og:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <meta name="twitter:card" content="<?= $ogImage !== '' ? 'summary_large_image' : 'summary' ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($metaDescription !== ''): ?>
        <meta name="twitter:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php if ($ogImage !== ''): ?>
        <meta name="twitter:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <?php foreach ($jsonLd as $schema): ?>
        <?php if (is_array($schema) && $schema !== []): ?>
            <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php include __DIR__ . '/favicons.php'; ?>
    <?php foreach ($cssAssets as $cssFile): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars(Url::versioned((string) $cssFile), ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<main>
    <?php if ($moduleView !== '' && is_file($appView)): ?>
        <?php include $appView; ?>
    <?php else: ?>
        <div class="container"><p>Vista no encontrada.</p></div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/footer.php'; ?>

<?php foreach ($jsAssets as $jsFile): ?>
    <script src="<?= htmlspecialchars(Url::versioned((string) $jsFile), ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>
</body>
</html>
