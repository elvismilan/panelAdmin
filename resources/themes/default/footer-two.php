<?php
$assetBase = \Core\Url::to(rtrim((string) ($_ENV['LOGIN_OPTION2_ASSET_BASE'] ?? '/assets/theme-two'), '/'));
$pageAssets = $pageAssets ?? ['css' => [], 'js' => []];
$jsAssets = is_array($pageAssets['js'] ?? null) ? $pageAssets['js'] : [];
?>
    <script>
      var HOST_URL = "https://preview.keenthemes.com/metronic/theme/html/tools/preview";
    </script>
    <script>
      var KTAppSettings = {
        "breakpoints": {"sm": 576, "md": 768, "lg": 992, "xl": 1200, "xxl": 1200},
        "font-family": "Poppins"
      };
    </script>
    <script src="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/plugins/global/plugins.bundle.js"></script>
    <script src="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/plugins/custom/prismjs/prismjs.bundle.js"></script>
    <script src="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/js/scripts.bundle.js"></script>
    <script src="<?= htmlspecialchars($assetBase, ENT_QUOTES, 'UTF-8') ?>/js/pages/custom/login/login-general.js"></script>
    <?php foreach ($jsAssets as $jsFile): ?>
      <script src="<?= htmlspecialchars($jsFile, ENT_QUOTES, 'UTF-8') ?>"></script>
    <?php endforeach; ?>
  </body>
</html>
