<?php
$assetBase = \Core\Url::to(rtrim((string) ($_ENV['LOGIN_OPTION1_ASSET_BASE'] ?? $_ENV['LOGIN_ASSET_BASE'] ?? '/assets/theme-one'), '/'));
$pageAssets = $pageAssets ?? ['css' => [], 'js' => []];
$jsAssets = is_array($pageAssets['js'] ?? null) ? $pageAssets['js'] : [];
?>
    <script src="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/js/jquery-3.5.1.min.js"></script>
    <script src="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/js/icons/feather-icon/feather.min.js"></script>
    <script src="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/js/icons/feather-icon/feather-icon.js"></script>
    <script src="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/js/sidebar-menu.js"></script>
    <script src="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/js/config.js"></script>
    <script src="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/js/bootstrap/popper.min.js"></script>
    <script src="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/js/bootstrap/bootstrap.min.js"></script>
    <script src="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/js/script.js"></script>
    <?php foreach ($jsAssets as $jsFile): ?>
      <script src="<?= htmlspecialchars($jsFile, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endforeach; ?>
  </body>
</html>