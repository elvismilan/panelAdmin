<?php
$title = $title ?? 'Web Revolution';
$assetBase = \PanelAdmin\Core\Url::to(rtrim((string) ($_ENV['LOGIN_OPTION1_ASSET_BASE'] ?? $_ENV['LOGIN_ASSET_BASE'] ?? '/assets/theme-one'), '/'));
$pageAssets = $pageAssets ?? ['css' => [], 'js' => []];
$cssAssets = is_array($pageAssets['css'] ?? null) ? $pageAssets['css'] : [];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="<?= htmlspecialchars($_ENV['ADMIN_META_DESCRIPTION'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($_ENV['ADMIN_META_KEYWORDS'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <meta name="author" content="<?= htmlspecialchars($_ENV['ADMIN_META_AUTHOR'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <?php include dirname(__DIR__, 3) . '/app/Views/shared/favicons.php'; ?>
    <title><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/css/fontawesome.css">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/css/icofont.css">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/css/themify.css">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/css/flag-icon.css">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/css/feather-icon.css">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/css/style.css">
    <link id="color" rel="stylesheet" href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/css/color-1.css" media="screen">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/css/responsive.css">
    <?php foreach ($cssAssets as $cssFile): ?>
      <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($cssFile, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
  </head>
  <body>
    <div class="loader-wrapper">
      <div class="theme-loader">
        <div class="loader-p"></div>
      </div>
    </div>