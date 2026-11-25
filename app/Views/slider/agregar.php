<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Nuevo slider</h5><span>Agregar registro</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>
                    <form method="post" action="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/slider/guardar'), ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data">
                        <?= $csrfField ?>

                        <div class="row">
                            <div class="col-md-7 mb-4">
                                <?php
                                $uploadFieldName = 'imagen';
                                $uploadLabel = 'Imagen';
                                $uploadCurrent = '';
                                $uploadNote = 'JPG, PNG, GIF, WEBP — max. 2 MB';
                                $uploadRecommended = 'Tamaño ideal: 1920 × 1080 px (16:9)';
                                $uploadId = 'slider_agregar';
                                include dirname(__DIR__) . '/components/image-upload.php';
                                ?>
                            </div>

                            <div class="col-md-5 mb-4">
                                <?php $overlayValue = (string) ($form['overlay'] ?? '0.50'); ?>
                                <label for="overlay" class="form-label d-flex align-items-center justify-content-between">
                                    <span>Overlay</span>
                                    <span id="overlay-value-agregar" class="badge badge-light-primary"><?= htmlspecialchars($overlayValue, ENT_QUOTES, 'UTF-8') ?></span>
                                </label>
                                <input
                                    id="overlay"
                                    class="<?= htmlspecialchars($adminFieldClass('overlay', 'form-control js-overlay-slider'), ENT_QUOTES, 'UTF-8') ?>"
                                    type="text"
                                    name="overlay"
                                    value="<?= htmlspecialchars($overlayValue, ENT_QUOTES, 'UTF-8') ?>"
                                    data-overlay-slider="1"
                                    data-output="#overlay-value-agregar">
                                <?php if ($adminFieldError('overlay') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('overlay'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="titulo" class="form-label">Titulo</label>
                                <input id="titulo" class="<?= htmlspecialchars($adminFieldClass('titulo'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="titulo" maxlength="128" value="<?= htmlspecialchars((string) ($form['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ($adminFieldError('titulo') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('titulo'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="subtitle" class="form-label">Subtitulo</label>
                                <input id="subtitle" class="<?= htmlspecialchars($adminFieldClass('subtitle'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="subtitle" maxlength="200" value="<?= htmlspecialchars((string) ($form['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ($adminFieldError('subtitle') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('subtitle'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Descripcion</label>
                                <textarea id="description" class="<?= htmlspecialchars($adminFieldClass('description'), ENT_QUOTES, 'UTF-8') ?>" name="description" rows="3"><?= htmlspecialchars((string) ($form['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                <?php if ($adminFieldError('description') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('description'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="nombre_btn" class="form-label">Nombre del boton</label>
                                <input id="nombre_btn" class="<?= htmlspecialchars($adminFieldClass('nombre_btn'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="nombre_btn" maxlength="128" value="<?= htmlspecialchars((string) ($form['nombre_btn'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ($adminFieldError('nombre_btn') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('nombre_btn'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="link_btn" class="form-label">Link del boton</label>
                                <input id="link_btn" class="<?= htmlspecialchars($adminFieldClass('link_btn'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="link_btn" maxlength="128" value="<?= htmlspecialchars((string) ($form['link_btn'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ($adminFieldError('link_btn') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('link_btn'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="orden" class="form-label">Orden (opcional)</label>
                                <input id="orden" class="<?= htmlspecialchars($adminFieldClass('orden'), ENT_QUOTES, 'UTF-8') ?>" type="number" name="orden" min="1" max="255" step="1" value="<?= htmlspecialchars((string) ($form['orden'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <small class="text-muted">Sugerido automáticamente. Si lo dejas vacío se ubicará al final.</small>
                                <?php if ($adminFieldError('orden') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('orden'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>
                        </div>

                        <input type="hidden" name="estado" value="H">

                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/slider'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
