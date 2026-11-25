<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Nueva red social</h5>
                    <span>Agregar registro</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>

                    <form method="post" action="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/redes/guardar'), ENT_QUOTES, 'UTF-8') ?>">
                        <?= $csrfField ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="res_titulo" class="form-label">Titulo</label>
                                <input id="res_titulo" class="<?= htmlspecialchars($adminFieldClass('res_titulo'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="res_titulo" maxlength="32" value="<?= htmlspecialchars((string) ($form['res_titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ($adminFieldError('res_titulo') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('res_titulo'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="res_icon" class="form-label">Icono (Font Awesome)</label>
                                <?php
                                $iconActual = (string) ($form['res_icon'] ?? '');
                                $iconPreviewMap = [
                                    'fa-brands fa-facebook' => 'fa fa-facebook',
                                    'fa-brands fa-facebook-f' => 'fa fa-facebook',
                                    'fa-brands fa-instagram' => 'fa fa-instagram',
                                    'fa-brands fa-x-twitter' => 'fa fa-twitter',
                                    'fa-brands fa-youtube' => 'fa fa-youtube-play',
                                    'fa-brands fa-tiktok' => 'fa fa-music',
                                    'fa-brands fa-linkedin-in' => 'fa fa-linkedin',
                                    'fa-brands fa-whatsapp' => 'fa fa-whatsapp',
                                    'fa-brands fa-telegram' => 'fa fa-telegram',
                                    'fa-brands fa-pinterest-p' => 'fa fa-pinterest-p',
                                    'fa-brands fa-snapchat' => 'fa fa-snapchat-ghost',
                                    'fa-brands fa-discord' => 'fa fa-comments',
                                    'fa-brands fa-twitch' => 'fa fa-gamepad',
                                    'fa-brands fa-reddit-alien' => 'fa fa-reddit-alien',
                                    'fa-brands fa-github' => 'fa fa-github',
                                    'fa-brands fa-gitlab' => 'fa fa-gitlab',
                                    'fa-brands fa-medium' => 'fa fa-medium',
                                    'fa-brands fa-vimeo-v' => 'fa fa-vimeo',
                                    'fa-brands fa-spotify' => 'fa fa-spotify',
                                    'fa-brands fa-apple' => 'fa fa-apple',
                                    'fa-brands fa-google' => 'fa fa-google',
                                    'fa-brands fa-threads' => 'fa fa-comments-o',
                                    'fa-brands fa-line' => 'fa fa-commenting-o',
                                    'fa-brands fa-weixin' => 'fa fa-wechat',
                                    'fa-brands fa-skype' => 'fa fa-skype',
                                    'fa-brands fa-slack' => 'fa fa-slack',
                                    'fa-brands fa-dribbble' => 'fa fa-dribbble',
                                    'fa-brands fa-behance' => 'fa fa-behance',
                                    'fa-brands fa-flickr' => 'fa fa-flickr',
                                    'fa-brands fa-wordpress' => 'fa fa-wordpress',
                                    'fa-solid fa-link' => 'fa fa-globe',
                                ];
                                $iconPreviewClass = $iconPreviewMap[$iconActual] ?? ($iconActual !== '' ? $iconActual : 'fa fa-globe');
                                ?>
                                <select id="res_icon" class="<?= htmlspecialchars($adminFieldClass('res_icon', 'form-select'), ENT_QUOTES, 'UTF-8') ?>" name="res_icon">
                                    <option value="">-- Seleccionar icono --</option>
                                    <?php foreach (($iconOptions ?? []) as $opt): ?>
                                        <?php
                                        $optValue = (string) ($opt['value'] ?? '');
                                        $optLabel = (string) ($opt['label'] ?? $optValue);
                                        ?>
                                        <option value="<?= htmlspecialchars($optValue, ENT_QUOTES, 'UTF-8') ?>" <?= $iconActual === $optValue ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($optLabel, ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($optValue, ENT_QUOTES, 'UTF-8') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    Vista previa:
                                    <i id="preview_res_icon" class="<?= htmlspecialchars($iconPreviewClass, ENT_QUOTES, 'UTF-8') ?> ms-1" aria-hidden="true"></i>
                                </div>
                                <?php if ($adminFieldError('res_icon') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('res_icon'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="res_link" class="form-label">Link</label>
                                <input id="res_link" class="<?= htmlspecialchars($adminFieldClass('res_link'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="res_link" maxlength="128" placeholder="https://..." value="<?= htmlspecialchars((string) ($form['res_link'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ($adminFieldError('res_link') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('res_link'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>
                        </div>

                        <input type="hidden" name="res_estado" value="H">

                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/redes'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light">Cancelar</a>
                    </form>
                    <script>
                        (function () {
                            var iconSelect = document.getElementById('res_icon');
                            var iconPreview = document.getElementById('preview_res_icon');
                            var iconMap = {
                                'fa-brands fa-facebook': 'fa fa-facebook',
                                'fa-brands fa-facebook-f': 'fa fa-facebook',
                                'fa-brands fa-instagram': 'fa fa-instagram',
                                'fa-brands fa-x-twitter': 'fa fa-twitter',
                                'fa-brands fa-youtube': 'fa fa-youtube-play',
                                'fa-brands fa-tiktok': 'fa fa-music',
                                'fa-brands fa-linkedin-in': 'fa fa-linkedin',
                                'fa-brands fa-whatsapp': 'fa fa-whatsapp',
                                'fa-brands fa-telegram': 'fa fa-telegram',
                                'fa-brands fa-pinterest-p': 'fa fa-pinterest-p',
                                'fa-brands fa-snapchat': 'fa fa-snapchat-ghost',
                                'fa-brands fa-discord': 'fa fa-comments',
                                'fa-brands fa-twitch': 'fa fa-gamepad',
                                'fa-brands fa-reddit-alien': 'fa fa-reddit-alien',
                                'fa-brands fa-github': 'fa fa-github',
                                'fa-brands fa-gitlab': 'fa fa-gitlab',
                                'fa-brands fa-medium': 'fa fa-medium',
                                'fa-brands fa-vimeo-v': 'fa fa-vimeo',
                                'fa-brands fa-spotify': 'fa fa-spotify',
                                'fa-brands fa-apple': 'fa fa-apple',
                                'fa-brands fa-google': 'fa fa-google',
                                'fa-brands fa-threads': 'fa fa-comments-o',
                                'fa-brands fa-line': 'fa fa-commenting-o',
                                'fa-brands fa-weixin': 'fa fa-wechat',
                                'fa-brands fa-skype': 'fa fa-skype',
                                'fa-brands fa-slack': 'fa fa-slack',
                                'fa-brands fa-dribbble': 'fa fa-dribbble',
                                'fa-brands fa-behance': 'fa fa-behance',
                                'fa-brands fa-flickr': 'fa fa-flickr',
                                'fa-brands fa-wordpress': 'fa fa-wordpress',
                                'fa-solid fa-link': 'fa fa-globe'
                            };
                            function updateIconPreview() {
                                if (!iconSelect || !iconPreview) return;
                                var selected = iconSelect.value.trim();
                                var previewClass = iconMap[selected] || (selected !== '' ? selected : 'fa fa-globe');
                                iconPreview.className = previewClass + ' ms-1';
                            }
                            if (iconSelect) {
                                iconSelect.addEventListener('change', updateIconPreview);
                                updateIconPreview();
                            }
                        })();
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
