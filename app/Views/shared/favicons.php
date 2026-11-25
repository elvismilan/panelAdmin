<?php
/**
 * Favicons del sitio publico. Reemplaza public/assets/public-theme/img/favicon.png
 * por el icono del proyecto (o cambia el enlace de aqui).
 */

use PanelAdmin\Core\Url;
?>
<link rel="icon" type="image/png" href="<?= htmlspecialchars(Url::to('/assets/public-theme/img/favicon.png'), ENT_QUOTES, 'UTF-8') ?>">
