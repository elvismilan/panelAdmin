<?php
$title = $title ?? 'Web Revolution';
$assetBase = \Core\Url::to(rtrim((string) ($_ENV['LOGIN_OPTION2_ASSET_BASE'] ?? '/assets/theme-two'), '/'));
$pageAssets = $pageAssets ?? ['css' => [], 'js' => []];
$cssAssets = is_array($pageAssets['css'] ?? null) ? $pageAssets['css'] : [];
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="Signin page" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/css/pages/login/classic/login-3.css" rel="stylesheet" type="text/css" />
    <link href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/plugins/custom/prismjs/prismjs.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <?php foreach ($cssAssets as $cssFile): ?>
      <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($cssFile, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
    <link rel="shortcut icon" href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/media/logos/favicon.ico" />
  </head>
  <body id="kt_body" style="background-image: url('<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/media/bg/bg-10.jpg')" class="quick-panel-right demo-panel-right offcanvas-right header-fixed subheader-enabled page-loading">