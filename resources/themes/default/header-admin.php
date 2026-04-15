<?php
use Core\Helpers\MenuHelper;

$adminAssetBase = rtrim((string) ($_ENV['ADMIN_ASSET_BASE'] ?? ($_ENV['LOGIN_OPTION1_ASSET_BASE'] ?? '/assets/theme-one')), '/');
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
  </head>
  <body>
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
            <div class="logo-wrapper"><a href="/dashboard"><img class="img-fluid" src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/images/logo/logo.png" alt=""></a></div>
            <div class="dark-logo-wrapper"><a href="/dashboard"><img class="img-fluid" src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/images/logo/dark-logo.png" alt=""></a></div>
            <div class="toggle-sidebar"><i class="status_toggle middle" data-feather="align-center" id="sidebar-toggle"></i></div>
          </div>
          
          <div class="nav-right col pull-right right-menu p-0 box-col-6">
            <ul class="nav-menus">
              <li><a class="text-dark" href="#!" onclick="javascript:toggleFullScreen()"><i data-feather="maximize"></i></a></li>
              
              <li class="onhover-dropdown">
                <div class="notification-box"><i data-feather="bell"></i><span class="dot-animated"></span></div>
                <ul class="notification-dropdown onhover-show-div">
                  <li>
                    <p class="f-w-700 mb-0">You have 3 Notifications<span class="pull-right badge badge-primary badge-pill">4</span></p>
                  </li>
                  <li class="noti-primary">
                    <div class="media"><span class="notification-bg bg-light-primary"><i data-feather="activity"> </i></span>
                      <div class="media-body">
                        <p>Delivery processing </p><span>10 minutes ago</span>
                      </div>
                    </div>
                  </li>
                  <li class="noti-secondary">
                    <div class="media"><span class="notification-bg bg-light-secondary"><i data-feather="check-circle"> </i></span>
                      <div class="media-body">
                        <p>Order Complete</p><span>1 hour ago</span>
                      </div>
                    </div>
                  </li>
                  <li class="noti-success">
                    <div class="media"><span class="notification-bg bg-light-success"><i data-feather="file-text"> </i></span>
                      <div class="media-body">
                        <p>Tickets Generated</p><span>3 hour ago</span>
                      </div>
                    </div>
                  </li>
                  <li class="noti-danger">
                    <div class="media"><span class="notification-bg bg-light-danger"><i data-feather="user-check"> </i></span>
                      <div class="media-body">
                        <p>Delivery Complete</p><span>6 hour ago</span>
                      </div>
                    </div>
                  </li>
                </ul>
              </li>
              <li>
                <div class="mode"><i class="fa fa-moon-o"></i></div>
              </li>
              <li class="onhover-dropdown"><i data-feather="message-square"></i>
                <ul class="chat-dropdown onhover-show-div">
                  <li>
                    <div class="media"><img class="img-fluid rounded-circle me-3" src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/images/user/4.jpg" alt="">
                      <div class="media-body"><span>Ain Chavez</span>
                        <p class="f-12 light-font">Do you want to go see movie?</p>
                      </div>
                      <p class="f-12">32 mins ago</p>
                    </div>
                  </li>
                  <li>
                    <div class="media"><img class="img-fluid rounded-circle me-3" src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/images/user/1.jpg" alt="">
                      <div class="media-body"><span>Erica Hughes</span>
                        <p class="f-12 light-font">Thank you for rating us.</p>
                      </div>
                      <p class="f-12">58 mins ago</p>
                    </div>
                  </li>
                  <li>
                    <div class="media"><img class="img-fluid rounded-circle me-3" src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/images/user/2.jpg" alt="">
                      <div class="media-body"><span>Kori Thomas</span>
                        <p class="f-12 light-font">What`s the project report update?</p>
                      </div>
                      <p class="f-12">1 hr ago</p>
                    </div>
                  </li>
                  <li class="text-center"> <a class="f-w-700" href="../template/chat.html">See All     </a></li>
                </ul>
              </li>
              <li class="onhover-dropdown p-0">
                <form method="post" action="/logout" class="m-0 p-0">
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
                   src="/<?= htmlspecialchars($adminUserPhoto, ENT_QUOTES, 'UTF-8') ?>"
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