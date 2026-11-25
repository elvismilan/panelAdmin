<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Nueva galeria</h5><span>Cabecera de la galeria: al guardar pasaras a cargar las fotos</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>
                    <form method="post" action="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/galerias/guardar'), ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data">
                        <?= $csrfField ?>

                        <div class="row">
                            <div class="col-md-5 mb-4">
                                <?php
                                $uploadFieldName = 'portada';
                                $uploadLabel = 'Portada';
                                $uploadCurrent = '';
                                $uploadNote = 'JPG, PNG, GIF, WEBP — max. 3 MB';
                                $uploadRecommended = 'Tamaño ideal: 1400 × 1000 px (7:5)';
                                $uploadId = 'galeria_agregar';
                                include dirname(__DIR__) . '/components/image-upload.php';
                                ?>
                            </div>

                            <div class="col-md-7">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input id="nombre" class="<?= htmlspecialchars($adminFieldClass('nombre'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="nombre" maxlength="100" value="<?= htmlspecialchars((string) ($form['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    <?php if ($adminFieldError('nombre') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('nombre'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fecha" class="form-label">Fecha</label>
                                        <input id="fecha"
                                               class="<?= htmlspecialchars($adminFieldClass('fecha', 'datepicker-here form-control digits'), ENT_QUOTES, 'UTF-8') ?>"
                                               type="text"
                                               name="fecha"
                                               data-language="en"
                                               data-date-format="yyyy-mm-dd"
                                               autocomplete="off"
                                               value="<?= htmlspecialchars((string) ($form['fecha'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        <?php if ($adminFieldError('fecha') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('fecha'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="estado" class="form-label">Estado</label>
                                        <select id="estado" class="<?= htmlspecialchars($adminFieldClass('estado', 'form-select'), ENT_QUOTES, 'UTF-8') ?>" name="estado">
                                            <?php $estadoValue = (string) ($form['estado'] ?? 'H'); ?>
                                            <option value="H"<?= $estadoValue === 'H' ? ' selected' : '' ?>>Habilitada</option>
                                            <option value="D"<?= $estadoValue === 'D' ? ' selected' : '' ?>>Deshabilitada</option>
                                        </select>
                                        <?php if ($adminFieldError('estado') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('estado'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="resumen" class="form-label">Resumen</label>
                                <textarea id="resumen" class="<?= htmlspecialchars($adminFieldClass('resumen'), ENT_QUOTES, 'UTF-8') ?>" name="resumen" rows="2"><?= htmlspecialchars((string) ($form['resumen'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                <?php if ($adminFieldError('resumen') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('resumen'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar y agregar fotos</button>
                        <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/galerias'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
