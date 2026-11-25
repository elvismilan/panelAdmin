<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Editar slider</h5><span>Actualizar registro existente</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>
                    <form method="post" action="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/slider/' . urlencode((string) ($itemId ?? '')) . '/actualizar'), ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data">
                        <?= $csrfField ?>

                        <div class="row">
                            <div class="col-lg-8 mb-4">
                                <?php
                                $uploadFieldName = 'imagen';
                                $uploadLabel = 'Imagen';
                                $uploadCurrent = (string) ($form['imagen'] ?? '');
                                $uploadNote = 'JPG, PNG, GIF, WEBP — max. 2 MB';
                                $uploadRecommended = 'Tamaño ideal: 1920 × 1080 px (16:9)';
                                $uploadId = 'slider_editar';
                                include dirname(__DIR__) . '/components/image-upload.php';
                                ?>
                            </div>

                            <div class="col-lg-4 mb-4">
                                <?php $overlayValue = (string) ($form['overlay'] ?? '0.50'); ?>
                                <label for="overlay" class="form-label d-flex align-items-center justify-content-between">
                                    <span>Overlay</span>
                                    <span id="overlay-value-editar" class="badge badge-light-primary"><?= htmlspecialchars($overlayValue, ENT_QUOTES, 'UTF-8') ?></span>
                                </label>
                                <input
                                    id="overlay"
                                    class="<?= htmlspecialchars($adminFieldClass('overlay', 'form-control js-overlay-slider'), ENT_QUOTES, 'UTF-8') ?>"
                                    type="text"
                                    name="overlay"
                                    value="<?= htmlspecialchars($overlayValue, ENT_QUOTES, 'UTF-8') ?>"
                                    data-overlay-slider="1"
                                    data-output="#overlay-value-editar">
                                <?php if ($adminFieldError('overlay') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('overlay'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

                                <div class="mt-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <?php $estado = (string) ($form['estado'] ?? 'H'); ?>
                                    <select id="estado" name="estado" class="<?= htmlspecialchars($adminFieldClass('estado', 'form-select'), ENT_QUOTES, 'UTF-8') ?>">
                                        <option value="H" <?= $estado === 'H' ? 'selected' : '' ?>>Habilitado (H)</option>
                                        <option value="D" <?= $estado === 'D' ? 'selected' : '' ?>>Deshabilitado (D)</option>
                                    </select>
                                    <?php if ($adminFieldError('estado') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('estado'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                </div>
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
                                <small class="text-muted">Si lo dejas vacío, se mantiene el orden actual.</small>
                                <?php if ($adminFieldError('orden') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('orden'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/slider'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
