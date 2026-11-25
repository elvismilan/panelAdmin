<div class="container-fluid">
    <?php
    $canAgregar = $can('/slider/agregar', 'agregar');
    $canEditar = $can('/slider/1/editar', 'editar');
    $canEliminar = $can('/slider/1/eliminar', 'eliminar');
    $canOrdenar = $can('/slider/1/ordenar', 'ordenar');
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Slider</h5>
                        <span>Listado de slides del frontend</span>
                    </div>

                    <?php if ($canAgregar): ?>
                        <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/slider/agregar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                            <span>Agregar</span>
                        </a>
                    <?php endif; ?>
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

                    <?php if (!empty($pagination['totalRows'])): ?>
                        <p class="text-muted small mb-2">
                            Mostrando <?= (int) ($pagination['from'] ?? 0) ?>-<?= (int) ($pagination['to'] ?? 0) ?>
                            de <?= (int) ($pagination['totalRows'] ?? 0) ?> registros
                        </p>
                    <?php endif; ?>

                    <div class="table-responsive"
                         data-reorder-container
                         data-reorder-csrf="<?= htmlspecialchars(\PanelAdmin\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                        <table class="table table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>#</th>
                                    <th>Titulo</th>
                                    <th>Subtitulo</th>
                                    <th>Orden</th>
                                    <th>Estado</th>
                                    <th class="text-center" style="width: 140px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                    <?php
                                    $emptyView = dirname(__DIR__) . '/components/admin-empty-state.php';
                                    $emptyTitle = !empty($search) ? 'Sin sliders para esta búsqueda' : 'No hay sliders registrados';
                                    $emptyMessage = !empty($search)
                                        ? 'Cambia el término o limpia la búsqueda para revisar todos los sliders configurados.'
                                        : 'Aquí aparecerán los slides del home con su orden, estado y acciones disponibles.';
                                    $emptyActionHref = !empty($search) ? '/slider' : ($canAgregar ? '/slider/agregar' : '');
                                    $emptyActionLabel = !empty($search) ? 'Limpiar búsqueda' : ($canAgregar ? 'Agregar slider' : '');
                                    $emptyColspan = 6;
                                    $emptyAsTableRow = true;
                                    if (is_file($emptyView)) {
                                        include $emptyView;
                                    }
                                    ?>
                                <?php else: ?>
                                    <?php foreach ($items as $item): ?>
                                        <?php $itemId = urlencode((string) ($item['id'] ?? '')); ?>
                                        <tr data-reorder-id="<?= htmlspecialchars((string) ($item['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                            <td><?= htmlspecialchars((string) ($item['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="d-flex align-items-center gap-2">
                                                <?php
                                                $imagenRaw = trim((string) ($item['imagen'] ?? ''));
                                                if ($imagenRaw !== '') {
                                                    $imagenNormalized = str_replace('\\', '/', $imagenRaw);
                                                    $imagenPath = str_contains($imagenNormalized, '/')
                                                        ? ltrim($imagenNormalized, '/')
                                                        : 'uploads/slider/' . basename($imagenNormalized);
                                                } else {
                                                    $imagenPath = '';
                                                }
                                                ?>
                                                <?php if ($imagenPath !== ''): ?>
                                                    <img src="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/' . $imagenPath), ENT_QUOTES, 'UTF-8') ?>"
                                                         alt="Imagen slider"
                                                         class="rounded"
                                                         style="width:40px;height:40px;object-fit:cover;">
                                                <?php else: ?>
                                                    <span class="text-muted"><i class="fa fa-image fa-lg" aria-hidden="true"></i></span>
                                                <?php endif; ?>
                                                <?= htmlspecialchars((string) ($item['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td><?= htmlspecialchars((string) ($item['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <?php if ($canOrdenar): ?>
                                                    <div class="d-inline-flex align-items-center gap-1">
                                                        <button type="button"
                                                                class="btn btn-light px-2 py-1 js-reorder-btn"
                                                                data-direction="up"
                                                                data-reorder-url="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/slider/' . $itemId . '/ordenar'), ENT_QUOTES, 'UTF-8') ?>"
                                                                title="Subir" aria-label="Subir">
                                                            <i class="fa fa-chevron-up" aria-hidden="true"></i>
                                                        </button>
                                                        <span class="badge badge-light-primary" data-orden-value><?= htmlspecialchars((string) ($item['orden'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                                        <button type="button"
                                                                class="btn btn-light px-2 py-1 js-reorder-btn"
                                                                data-direction="down"
                                                                data-reorder-url="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/slider/' . $itemId . '/ordenar'), ENT_QUOTES, 'UTF-8') ?>"
                                                                title="Bajar" aria-label="Bajar">
                                                            <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <span data-orden-value><?= htmlspecialchars((string) ($item['orden'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                                <?php endif; ?>
                                            </td>
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
                                                    <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/slider/' . $itemId . '/editar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-warning px-2 py-1" title="Editar" aria-label="Editar">
                                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($canEliminar): ?>
                                                    <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/slider/' . $itemId . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-danger px-2 py-1" title="Eliminar" aria-label="Eliminar">
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
                    $paginationAriaLabel = 'Paginacion slider';
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
