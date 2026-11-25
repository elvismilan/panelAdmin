<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Nuevo contenido</h5>
                    <span>Agregar registro</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>

                    <form method="post" action="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/contenidos/guardar'), ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data">
                        <?= $csrfField ?>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <?php
                                $uploadFieldName = 'imagen';
                                $uploadLabel = 'Imagen';
                                $uploadCurrent = '';
                                $uploadNote = 'JPG, PNG, GIF, WEBP — max. 3 MB';
                                $uploadRecommended = 'Tamaño ideal: 1200 × 800 px (3:2)';
                                $uploadId = 'contenidos_agregar';
                                include dirname(__DIR__) . '/components/image-upload.php';
                                ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="lugar" class="form-label">Lugar</label>
                                        <input id="lugar" class="<?= htmlspecialchars($adminFieldClass('lugar'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="lugar" maxlength="64" value="<?= htmlspecialchars((string) ($form['lugar'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        <?php if ($adminFieldError('lugar') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('lugar'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <label for="link" class="form-label">Link</label>
                                        <input id="link" class="<?= htmlspecialchars($adminFieldClass('link'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="link" maxlength="255" placeholder="https://..." value="<?= htmlspecialchars((string) ($form['link'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        <?php if ($adminFieldError('link') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('link'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="titulo" class="form-label">Nombre contenido</label>
                                <input id="titulo" class="<?= htmlspecialchars($adminFieldClass('titulo'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="titulo" maxlength="128" value="<?= htmlspecialchars((string) ($form['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ($adminFieldError('titulo') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('titulo'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="subtitle" class="form-label">Subtitulo</label>
                                <input id="subtitle" class="<?= htmlspecialchars($adminFieldClass('subtitle'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="subtitle" maxlength="300" value="<?= htmlspecialchars((string) ($form['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ($adminFieldError('subtitle') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('subtitle'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="contenido" class="form-label">Contenido</label>
                                <textarea id="contenido" class="<?= htmlspecialchars($adminFieldClass('contenido', 'form-control js-contenidos-editor'), ENT_QUOTES, 'UTF-8') ?>" name="contenido" rows="8"><?= htmlspecialchars((string) ($form['contenido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                <?php if ($adminFieldError('contenido') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('contenido'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>
                        </div>

                        <input type="hidden" name="estado" value="H">

                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/contenidos'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
