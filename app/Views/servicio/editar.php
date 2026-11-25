<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Editar servicio</h5><span>Actualizar registro</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>
                    <form method="post" action="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/servicios/' . urlencode((string) ($itemId ?? '')) . '/actualizar'), ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data">
                        <?= $csrfField ?>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <?php
                                $uploadFieldName = 'imagen';
                                $uploadLabel = 'Imagen';
                                $uploadCurrent = (string) ($form['imagen_preview'] ?? '');
                                $uploadNote = 'JPG, PNG, GIF, WEBP — max. 3 MB';
                                $uploadRecommended = 'Tamaño ideal: 1200 × 800 px (3:2)';
                                $uploadId = 'servicio_editar';
                                include dirname(__DIR__) . '/components/image-upload.php';
                                ?>
                            </div>

                            <div class="col-md-6 mb-4">
                                <?php
                                $uploadFieldName = 'icono';
                                $uploadLabel = 'Icono';
                                $uploadCurrent = (string) ($form['icon_preview'] ?? '');
                                $uploadNote = 'JPG, PNG, GIF, WEBP — max. 1 MB';
                                $uploadRecommended = 'Tamaño ideal: 152 × 152 px (1:1)';
                                $uploadId = 'servicio_icono_editar';
                                include dirname(__DIR__) . '/components/image-upload.php';
                                ?>

                                <?php /* Icon-picker (clase CSS) retirado: el icono ahora se sube como imagen, ver componente image-upload de arriba.
                                <div class="mb-3">
                                    <?php
                                    $iconActual = (string) ($form['icon'] ?? '');
                                    $pickerId = 'servicio_icon';
                                    $pickerName = 'icon';
                                    include dirname(__DIR__) . '/components/icon-picker.php';
                                    ?>
                                    <?php if ($adminFieldError('icon') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('icon'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                </div>
                                */ ?>
                            </div>

                            <div class="col-md-5 mb-3">
                                <label for="titulo" class="form-label">Titulo</label>
                                <input id="titulo" class="<?= htmlspecialchars($adminFieldClass('titulo'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="titulo" maxlength="255" value="<?= htmlspecialchars((string) ($form['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ($adminFieldError('titulo') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('titulo'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>

                            <div class="col-md-5 mb-3">
                                <label for="subtitulo" class="form-label">Subtitulo</label>
                                <input id="subtitulo" class="<?= htmlspecialchars($adminFieldClass('subtitulo'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="subtitulo" maxlength="255" value="<?= htmlspecialchars((string) ($form['subtitulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ($adminFieldError('subtitulo') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('subtitulo'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>

                            <div class="col-md-2 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select id="estado" class="<?= htmlspecialchars($adminFieldClass('estado', 'form-select'), ENT_QUOTES, 'UTF-8') ?>" name="estado">
                                    <?php $estadoValue = (string) ($form['estado'] ?? 'H'); ?>
                                    <option value="H"<?= $estadoValue === 'H' ? ' selected' : '' ?>>Habilitado</option>
                                    <option value="D"<?= $estadoValue === 'D' ? ' selected' : '' ?>>Deshabilitado</option>
                                </select>
                                <?php if ($adminFieldError('estado') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('estado'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="contenido" class="form-label">Contenido</label>
                                <textarea id="contenido" class="<?= htmlspecialchars($adminFieldClass('contenido', 'form-control js-contenidos-editor'), ENT_QUOTES, 'UTF-8') ?>" name="contenido" rows="8"><?= htmlspecialchars((string) ($form['contenido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                <?php if ($adminFieldError('contenido') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('contenido'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/servicios'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
