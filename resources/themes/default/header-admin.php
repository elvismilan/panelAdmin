<?php
use Core\Helpers\MenuHelper;
use Core\Helpers\NotificationHelper;

$adminAssetBase = \Core\Url::to(rtrim((string) ($_ENV['ADMIN_ASSET_BASE'] ?? ($_ENV['LOGIN_OPTION1_ASSET_BASE'] ?? '/assets/theme-one')), '/'));
$adminUserName  = trim((string) ($user['full_name'] ?? $user['username'] ?? 'Usuario'));
$adminUserPhoto = trim((string) ($user['photo'] ?? ''));
$adminGroupName = trim((string) ($user['group_name'] ?? $user['group'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($_ENV['ADMIN_META_DESCRIPTION'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($_ENV['ADMIN_META_KEYWORDS'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <meta name="author" content="<?= htmlspecialchars($_ENV['ADMIN_META_AUTHOR'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="icon" href="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/images/favicon.png" type="image/x-icon">
    <link rel="shortcut icon" href="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/images/favicon.png" type="image/x-icon">
    <title><?= htmlspecialchars($title ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?></title>
    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
    <!-- Font Awesome-->
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/css/fontawesome.css">
    <!-- ico-font-->
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/css/icofont.css">
    <!-- Themify icon-->
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/css/themify.css">
    <!-- Flag icon-->
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/css/flag-icon.css">
    <!-- Feather icon-->
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/css/feather-icon.css">
    <!-- Plugins css start-->
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/css/date-picker.css">
    <?php foreach (($pageAssets['css'] ?? []) as $_css): ?>
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars((string) $_css, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/css/bootstrap.css">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/css/style.css">
    <link id="color" rel="stylesheet" href="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/css/color-1.css" media="screen">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/css/responsive.css">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/css/glass-admin.css">
  </head>
  <body class="admin-theme">
    <!-- Loader starts-->
    <div class="loader-wrapper">
      <div class="theme-loader">    
        <div class="loader-p"></div>
      </div>
    </div>
    <!-- Loader ends-->
    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper modern-sidebar" id="pageWrapper">
      <!-- Page Header Start-->
      <div class="page-main-header">
        <div class="main-header-right row m-0">
          <div class="main-header-left">
            <div class="logo-wrapper"><a href="<?= htmlspecialchars(\Core\Url::to('/dashboard'), ENT_QUOTES, 'UTF-8') ?>"><img class="img-fluid" src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/images/logo/logo.png" alt=""></a></div>
            <div class="dark-logo-wrapper"><a href="<?= htmlspecialchars(\Core\Url::to('/dashboard'), ENT_QUOTES, 'UTF-8') ?>"><img class="img-fluid" src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/images/logo/dark-logo.png" alt=""></a></div>
            <div class="toggle-sidebar"><i class="status_toggle middle" data-feather="align-center" id="sidebar-toggle"></i></div>
          </div>
          
          <div class="nav-right col pull-right right-menu p-0 box-col-6">
            <ul class="nav-menus">
              <li><a class="text-dark" href="#!" onclick="javascript:toggleFullScreen()"><i data-feather="maximize"></i></a></li>
              <li>
                <a class="text-dark glass-toggle-btn"
                   href="#!"
                   id="glass-theme-toggle"
                   aria-label="Activar o desactivar glass theme"
                   title="Glass theme">
                  <i data-feather="layers"></i>
                </a>
              </li>
              <?= NotificationHelper::renderDropdown() ?>
              <li>
                <div class="mode"><i class="fa fa-moon-o"></i></div>
              </li>
              
              <li class="onhover-dropdown p-0">
                <form method="post" action="<?= htmlspecialchars(\Core\Url::to('/logout'), ENT_QUOTES, 'UTF-8') ?>" class="m-0 p-0">
                  <?= \Core\Csrf::field() ?>
                  <button class="btn btn-primary-light" type="submit"><i data-feather="log-out"></i>Cerrar Sesión</button>
                </form>
              </li>
            </ul>
          </div>
          <div class="d-lg-none mobile-toggle pull-right w-auto"><i data-feather="more-horizontal"></i></div>
        </div>
      </div>
      <!-- Page Header Ends                              -->
      <!-- Page Body Start-->
      <div class="page-body-wrapper sidebar-icon">
        <!-- Page Sidebar Start-->
        <header class="main-nav">
          <div class="sidebar-user text-center">
            <a class="setting-primary" href="javascript:void(0)"><i data-feather="settings"></i></a>
            <?php if ($adminUserPhoto !== ''): ?>
              <img class="img-70 rounded-circle"
                   src="<?= htmlspecialchars(\Core\Url::to('/' . ltrim((string) $adminUserPhoto, '/')), ENT_QUOTES, 'UTF-8') ?>"
                   alt="<?= htmlspecialchars($adminUserName, ENT_QUOTES, 'UTF-8') ?>"
                   style="object-fit:cover;">
            <?php else: ?>
              <img class="img-70 rounded-circle"
                   src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/images/dashboard/1.png"
                   alt="<?= htmlspecialchars($adminUserName, ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <a href="#">
              <h6 class="mt-3 f-14 f-w-600"><?= htmlspecialchars($adminUserName, ENT_QUOTES, 'UTF-8') ?></h6>
            </a>
            <?php if ($adminGroupName !== ''): ?>
            <p class="mb-0 font-roboto"><?= htmlspecialchars($adminGroupName, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
          </div>
          <nav>
            <div class="main-navbar">
              <div class="left-arrow" id="left-arrow"><i class="fa fa-angle-left" aria-hidden="true"></i></div>
              <div id="mainnav">           
                <ul class="nav-menu custom-scrollbar">
                  <li class="back-btn">
                    <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
                  </li>

                  <?php
                  $adminMenu = is_array($adminMenu ?? null) ? $adminMenu : [];
                  $currentPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
                  ?>
                  <?= MenuHelper::renderAdminMenu($adminMenu, (string) $currentPath) ?>
                </ul>
              </div>
              <div class="right-arrow" id="right-arrow"><i class="fa fa-angle-right" aria-hidden="true"></i></div>
            </div>
          </nav>
        </header>
        <!-- Page Sidebar Ends-->
        <div class="page-body">
