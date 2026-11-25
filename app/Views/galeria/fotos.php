<div class="container-fluid">
    <?php
    $galeria = is_array($galeria ?? null) ? $galeria : [];
    $fotos = is_array($fotos ?? null) ? $fotos : [];
    $galeriaId = urlencode((string) ($itemId ?? ''));
    $portadaRaw = trim((string) ($galeria['portada'] ?? ''));
    $portadaPath = $portadaRaw !== ''
        ? \PanelAdmin\Core\Helpers\ImageHelper::thumb($portadaRaw, 'galeria', '150x150')
        : '';
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <?php if ($portadaPath !== ''): ?>
                            <img src="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/' . $portadaPath), ENT_QUOTES, 'UTF-8') ?>"
                                 alt="<?= htmlspecialchars((string) ($galeria['nombre'] ?? 'Portada'), ENT_QUOTES, 'UTF-8') ?>"
                                 class="rounded"
                                 style="width:56px;height:56px;object-fit:cover;">
                        <?php endif; ?>
                        <div>
                            <h5><?= htmlspecialchars((string) ($galeria['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h5>
                            <span>Fotos de la galeria (<?= count($fotos) ?>)</span>
                        </div>
                    </div>
                    <div class="btn-group" role="group" aria-label="Acciones de galeria">
                        <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/galerias/' . $galeriaId . '/editar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-warning btn-sm d-inline-flex align-items-center">
                            <i class="fa fa-pencil me-1" aria-hidden="true"></i><span>Editar cabecera</span>
                        </a>
                        <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/galerias'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light btn-sm d-inline-flex align-items-center">
                            <span>Volver</span>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    $flashView = dirname(__DIR__) . '/components/flash-messages.php';
                    if (is_file($flashView)) {
                        include $flashView;
                    }
                    ?>

                    <form method="post" action="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/galerias/' . $galeriaId . '/fotos/guardar'), ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data" class="mb-4">
                        <?= $csrfField ?>
                        <div class="row align-items-start">
                            <div class="col-md-6 mb-3">
                                <?php
                                $uploadFieldName = 'imagen';
                                $uploadLabel = 'Nueva foto';
                                $uploadCurrent = '';
                                $uploadNote = 'JPG, PNG, GIF, WEBP — max. 3 MB';
                                $uploadRecommended = 'Tamaño ideal: 1600 × 1200 px (4:3)';
                                $uploadId = 'galeria_foto';
                                include dirname(__DIR__) . '/components/image-upload.php';
                                ?>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="igf_alt" class="form-label">Nombre de la foto</label>
                                    <input id="igf_alt" class="form-control" type="text" name="igf_alt" maxlength="100" placeholder="Se usa como texto alternativo (alt)">
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-upload me-1" aria-hidden="true"></i>Subir foto
                                </button>
                            </div>
                        </div>
                    </form>

                    <hr>

                    <?php if ($fotos === []): ?>
                        <p class="text-center text-muted py-4 mb-0">Esta galeria aun no tiene fotos. Sube la primera con el formulario de arriba.</p>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($fotos as $foto): ?>
                                <?php
                                $fotoId = urlencode((string) ($foto['igf_id'] ?? ''));
                                $imagenRaw = trim((string) ($foto['igf_image'] ?? ''));
                                $alt = (string) ($foto['igf_alt'] ?? '');
                                $thumbPath = $imagenRaw !== ''
                                    ? \PanelAdmin\Core\Helpers\ImageHelper::thumb($imagenRaw, 'galeria', '403x290')
                                    : '';
                                $fullPath = $imagenRaw !== ''
                                    ? \PanelAdmin\Core\Helpers\ImageHelper::thumb($imagenRaw, 'galeria', '1024x768')
                                    : '';
                                ?>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="card border h-100 mb-0">
                                        <?php if ($thumbPath !== ''): ?>
                                            <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/' . $fullPath), ENT_QUOTES, 'UTF-8') ?>"
                                               class="js-lightbox"
                                               data-lightbox-group="galeria-fotos"
                                               data-caption="<?= htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') ?>"
                                               title="Ver foto">
                                                <img src="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/' . $thumbPath), ENT_QUOTES, 'UTF-8') ?>"
                                                     alt="<?= htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') ?>"
                                                     class="card-img-top"
                                                     style="height:150px;object-fit:cover;">
                                            </a>
                                        <?php endif; ?>
                                        <div class="card-body p-2 d-flex justify-content-between align-items-center gap-2">
                                            <span class="small text-truncate" title="<?= htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                            <form method="post"
                                                  action="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/galerias/' . $galeriaId . '/fotos/' . $fotoId . '/borrar'), ENT_QUOTES, 'UTF-8') ?>"
                                                  onsubmit="return confirm('¿Eliminar esta foto?');"
                                                  class="d-inline">
                                                <?= $csrfField ?>
                                                <button type="submit" class="btn btn-danger px-2 py-1" title="Eliminar foto" aria-label="Eliminar foto">
                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
