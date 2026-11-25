<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Editar blog</h5>
                    <span>Actualizar registro existente</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>

                    <form method="post" action="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/blogs/' . urlencode((string) ($itemId ?? '')) . '/actualizar'), ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data">
                        <?= $csrfField ?>
                        <?php
                        $categoryOptions = is_array($categoryOptions ?? null) ? $categoryOptions : [];
                        $selectedCategoryIds = array_map('strval', array_values(array_unique(array_map(
                            static fn($v): string => (string) $v,
                            is_array($form['post_category_ids'] ?? null) ? $form['post_category_ids'] : []
                        ))));
                        ?>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <?php
                                $uploadFieldName = 'imagen';
                                $uploadLabel = 'Imagen';
                                $uploadCurrent = (string) ($form['imagen'] ?? '');
                                $uploadNote = 'JPG, PNG, GIF, WEBP — max. 3 MB';
                                $uploadRecommended = 'Tamaño ideal: 1600 × 900 px (16:9)';
                                $uploadId = 'blogs_editar';
                                include dirname(__DIR__) . '/components/image-upload.php';
                                ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="autor" class="form-label">Autor</label>
                                        <select id="autor" name="autor" class="<?= htmlspecialchars($adminFieldClass('autor', 'form-select'), ENT_QUOTES, 'UTF-8') ?>">
                                            <?php $selectedAuthor = trim((string) ($form['autor'] ?? '')); ?>
                                            <?php foreach (($authorOptions ?? []) as $authorOption): ?>
                                                <?php $authorValue = (string) ($authorOption['value'] ?? ''); ?>
                                                <option value="<?= htmlspecialchars($authorValue, ENT_QUOTES, 'UTF-8') ?>" <?= $authorValue === $selectedAuthor ? 'selected' : '' ?>><?= htmlspecialchars((string) ($authorOption['label'] ?? $authorValue), ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if ($adminFieldError('autor') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('autor'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col mb-3">
                                        <label for="post_category_ids" class="form-label">Categorias</label>
                                        <select id="post_category_ids" name="post_category_ids[]" class="<?= htmlspecialchars($adminFieldClass('post_category_ids', 'form-select js-blog-categories-select'), ENT_QUOTES, 'UTF-8') ?>" multiple data-placeholder="Selecciona una o varias categorias">
                                            <?php foreach ($categoryOptions as $category): ?>
                                                <?php
                                                $catId = (string) ($category['cat_id'] ?? '');
                                                $catName = (string) ($category['cat_name'] ?? '');
                                                ?>
                                                <option value="<?= htmlspecialchars($catId, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($catId, $selectedCategoryIds, true) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted d-block mt-1">Puedes seleccionar varias categorias (opcional).</small>
                                        <?php if ($adminFieldError('post_category_ids') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('post_category_ids'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                    </div>

                                    <div class="col-12 mb-0">
                                        <label for="estado" class="form-label">Estado</label>
                                        <?php $status = (string) ($form['estado'] ?? 'H'); ?>
                                        <select id="estado" name="estado" class="<?= htmlspecialchars($adminFieldClass('estado', 'form-select'), ENT_QUOTES, 'UTF-8') ?>">
                                            <option value="H" <?= $status === 'H' ? 'selected' : '' ?>>Habilitado (H)</option>
                                            <option value="D" <?= $status === 'D' ? 'selected' : '' ?>>Deshabilitado (D)</option>
                                        </select>
                                        <?php if ($adminFieldError('estado') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('estado'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="titulo" class="form-label">Titulo</label>
                                <input id="titulo" class="<?= htmlspecialchars($adminFieldClass('titulo'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="titulo" maxlength="255" value="<?= htmlspecialchars((string) ($form['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ($adminFieldError('titulo') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('titulo'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="slug" class="form-label">Slug (fijo: la URL no cambia al editar)</label>
                                <input id="slug" class="<?= htmlspecialchars($adminFieldClass('slug'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="slug" maxlength="255" readonly data-slug-locked value="<?= htmlspecialchars((string) ($form['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ($adminFieldError('slug') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('slug'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="detalle" class="form-label">Resumen</label>
                                <textarea id="detalle" class="<?= htmlspecialchars($adminFieldClass('detalle'), ENT_QUOTES, 'UTF-8') ?>" name="detalle" rows="2" maxlength="160"><?= htmlspecialchars((string) ($form['detalle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                <?php if ($adminFieldError('detalle') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('detalle'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="contenido" class="form-label">Contenido</label>
                                <textarea id="contenido" class="<?= htmlspecialchars($adminFieldClass('contenido', 'form-control js-blogs-editor'), ENT_QUOTES, 'UTF-8') ?>" name="contenido" rows="8"><?= htmlspecialchars((string) ($form['contenido'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                <?php if ($adminFieldError('contenido') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('contenido'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/blogs'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
