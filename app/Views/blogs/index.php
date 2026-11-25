<div class="container-fluid">
    <?php
    $blogCategories = is_array($blogCategories ?? null) ? $blogCategories : [];

    $canAgregar = $can('/blogs/agregar', 'agregar');
    $canEditar = $can('/blogs/1/editar', 'editar');
    $canEliminar = $can('/blogs/1/eliminar', 'eliminar');
    $canGestionarCategorias = $can('/blogs/categorias/agregar', 'guardarCategoria');
    $canEliminarCategorias = $can('/blogs/categorias/1/eliminar', 'borrarCategoria');
    ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Blogs</h5>
                        <span>Listado de blogs</span>
                    </div>

                    <div class="d-flex gap-2">
                        <?php if ($canGestionarCategorias): ?>
                            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#blogsCategoriasPanel" aria-expanded="false" aria-controls="blogsCategoriasPanel">
                                Categorias
                            </button>
                        <?php endif; ?>
                        <?php if ($canAgregar): ?>
                            <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/blogs/agregar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                                <span>Agregar</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body pt-3">
                    <?php
                    $listToolbarConfig = [
                        'mode' => 'simple',
                        'searchConfig' => is_array($searchConfig ?? null) ? $searchConfig : [],
                    ];
                    $toolbarView = dirname(__DIR__) . '/components/list-toolbar.php';
                    if (is_file($toolbarView)) {
                        include $toolbarView;
                    }

                    $flashView = dirname(__DIR__) . '/components/flash-messages.php';
                    if (is_file($flashView)) {
                        include $flashView;
                    }
                    ?>

                    <?php if ($canGestionarCategorias): ?>
                        <div class="collapse" id="blogsCategoriasPanel">
                            <div class="card card-body mb-3">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <h6 class="mb-2">Agregar categoria</h6>
                                        <form method="post" action="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/blogs/categorias/agregar'), ENT_QUOTES, 'UTF-8') ?>">
                                            <?= $csrfField ?>
                                            <div class="input-group">
                                                <input type="text" name="cat_name" class="form-control" maxlength="150" placeholder="Ej: Noticias" required>
                                                <button type="submit" class="btn btn-primary">Guardar</button>
                                            </div>
                                            <small class="text-muted d-block mt-2">Tipo asignado a Blog.</small>
                                        </form>
                                    </div>
                                    <div class="col-md-7">
                                        <h6 class="mb-2">Categorias</h6>
                                        <?php if ($blogCategories === []): ?>
                                            <p class="text-muted mb-0">Aun no hay categorias.</p>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Nombre</th>
                                                            <th>Slug</th>
                                                            <th>Estado</th>
                                                            <th class="text-end">Accion</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($blogCategories as $category): ?>
                                                            <?php
                                                            $catId = (string) ($category['cat_id'] ?? '');
                                                            $catStatus = (string) ($category['cat_status'] ?? 'H');
                                                            ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars((string) ($category['cat_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td><code><?= htmlspecialchars((string) ($category['cat_slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></code></td>
                                                                <td>
                                                                    <?php if ($catStatus === 'H'): ?>
                                                                        <span class="badge bg-success text-white">H</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-secondary text-white"><?= htmlspecialchars($catStatus, ENT_QUOTES, 'UTF-8') ?></span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-end">
                                                                    <?php if ($canEliminarCategorias): ?>
                                                                        <form method="post" action="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/blogs/categorias/' . urlencode($catId) . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>" class="d-inline">
                                                                            <?= $csrfField ?>
                                                                            <button type="submit" class="btn btn-danger px-2 py-1" title="Eliminar" aria-label="Eliminar" onclick="return confirm('¿Eliminar categoria?')">
                                                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                                                            </button>
                                                                        </form>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">-</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($pagination['totalRows'])): ?>
                        <p class="text-muted small mb-2">
                            Mostrando <?= (int) ($pagination['from'] ?? 0) ?>-<?= (int) ($pagination['to'] ?? 0) ?>
                            de <?= (int) ($pagination['totalRows'] ?? 0) ?> registros
                        </p>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>#</th>
                                    <th>Titulo</th>
                                    <th>Autor</th>
                                    <th>Estado</th>
                                    <th class="text-center" style="width: 140px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                    <?php
                                    $emptyView = dirname(__DIR__) . '/components/admin-empty-state.php';
                                    $emptyTitle = !empty($search) ? 'Sin blogs para esta búsqueda' : 'No hay blogs registrados';
                                    $emptyMessage = !empty($search)
                                        ? 'Prueba con otro término o limpia la búsqueda para revisar todas las publicaciones.'
                                        : 'Aquí aparecerán los artículos del blog con su autor, estado y acciones disponibles.';
                                    $emptyActionHref = !empty($search) ? '/blogs' : ($canAgregar ? '/blogs/agregar' : '');
                                    $emptyActionLabel = !empty($search) ? 'Limpiar búsqueda' : ($canAgregar ? 'Agregar blog' : '');
                                    $emptyColspan = 5;
                                    $emptyAsTableRow = true;
                                    if (is_file($emptyView)) {
                                        include $emptyView;
                                    }
                                    ?>
                                <?php else: ?>
                                    <?php foreach ($items as $item): ?>
                                        <?php $itemId = urlencode((string) ($item['id'] ?? '')); ?>
                                        <tr>
                                            <td><?= htmlspecialchars((string) ($item['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="d-flex align-items-center gap-2">
                                                <?php
                                                $imagenRaw = trim((string) ($item['imagen'] ?? ''));
                                                $imagenPath = $imagenRaw !== ''
                                                    ? \PanelAdmin\Core\Helpers\ImageHelper::thumb($imagenRaw, 'blogs', '150x150')
                                                    : '';
                                                ?>
                                                <?php if ($imagenPath !== ''): ?>
                                                    <img src="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/' . $imagenPath), ENT_QUOTES, 'UTF-8') ?>"
                                                         alt="Imagen blog"
                                                         class="rounded"
                                                         style="width:40px;height:40px;object-fit:cover;">
                                                <?php else: ?>
                                                    <span class="text-muted"><i class="fa fa-image fa-lg" aria-hidden="true"></i></span>
                                                <?php endif; ?>
                                                <div>
                                                    <div><?= htmlspecialchars((string) ($item['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                                    <?php
                                                    $rawCategories = trim((string) ($item['category_names_raw'] ?? ''));
                                                    $categories = $rawCategories !== '' ? array_values(array_filter(array_map('trim', explode('||', $rawCategories)))) : [];
                                                    ?>
                                                    <?php if ($categories === []): ?>
                                                        <small class="text-muted">Sin categorias</small>
                                                    <?php else: ?>
                                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                                            <?php foreach ($categories as $categoryName): ?>
                                                                <span class="badge bg-light text-dark border" style="font-size:9px;font-weight:600;padding:0.24em 0.7em;"><?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?></span>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars((string) ($item['autor'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <?php $estado = (string) ($item['estado'] ?? ''); ?>
                                                <?php if ($estado === 'H'): ?>
                                                    <span class="badge bg-success text-white">Habilitado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary text-white">Deshabilitado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($canEditar): ?>
                                                    <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/blogs/' . $itemId . '/editar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-warning px-2 py-1" title="Editar" aria-label="Editar">
                                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($canEliminar): ?>
                                                    <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/blogs/' . $itemId . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-danger px-2 py-1" title="Eliminar" aria-label="Eliminar">
                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!$canEditar && !$canEliminar): ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php
                    $paginationAriaLabel = 'Paginacion blogs';
                    $paginationClass = 'pagination justify-content-center pagination-primary pagination-sm';
                    $paginationView = dirname(__DIR__) . '/components/pagination.php';
                    if (is_file($paginationView)) {
                        include $paginationView;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
