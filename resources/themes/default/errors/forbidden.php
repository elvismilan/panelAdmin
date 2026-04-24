<?php
$title = $title ?? 'Acceso denegado';
$assetBase = \Core\Url::to(rtrim((string) ($_ENV['LOGIN_OPTION1_ASSET_BASE'] ?? $_ENV['LOGIN_ASSET_BASE'] ?? '/assets/theme-one'), '/'));
$heading = $heading ?? 'No tienes permiso para esta accion';
$message = $message ?? 'No cuentas con permisos para continuar.';
$homeUrl = $homeUrl ?? \Core\Url::to('/dashboard');
$code = (int) ($code ?? 403);

$headerFile = dirname(__DIR__) . '/auth-layout-header.php';
$footerFile = dirname(__DIR__) . '/auth-layout-footer.php';

if (is_file($headerFile)) {
    include $headerFile;
}
?>
<div class="page-wrapper" id="pageWrapper">
  <div class="error-wrapper">
    <div class="container text-center">
      <div class="error-page1">
        <div class="mb-4">
          <img src="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/images/other-images/sad.png"
               alt="Acceso denegado"
               style="max-width:160px;width:100%;height:auto;">
        </div>
        <h1 class="mb-2" style="font-size:72px;line-height:1;"><?= htmlspecialchars((string) $code, ENT_QUOTES, 'UTF-8') ?></h1>
        <h3><?= htmlspecialchars((string) $heading, ENT_QUOTES, 'UTF-8') ?></h3>
        <p class="sub-content"><?= htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8') ?></p>
        <a class="btn btn-primary btn-lg" href="<?= htmlspecialchars((string) $homeUrl, ENT_QUOTES, 'UTF-8') ?>">Volver al inicio</a>
      </div>
    </div>
  </div>
</div>
<?php
if (is_file($footerFile)) {
    include $footerFile;
}
?>