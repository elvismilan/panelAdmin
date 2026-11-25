<div class="container-fluid">
    <?php
    $canAgregar = $can('/contenidos/agregar', 'agregar');
    $canEditar = $can('/contenidos/1/editar', 'editar');
    $canEliminar = $can('/contenidos/1/eliminar', 'eliminar');
    ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Contenidos</h5>
                        <span>Listado de contenidos</span>
                    </div>

                    <?php if ($canAgregar): ?>
                        <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/contenidos/agregar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center">
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

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>#</th>
                                    <th>Contenido</th>
                                    <th>Lugar</th>
                                    <th>Estado</th>
                                    <th class="text-center" style="width: 120px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                    <?php
                                    $emptyView = dirname(__DIR__) . '/components/admin-empty-state.php';
                                    $emptyTitle = !empty($search) ? 'Sin contenidos para esta búsqueda' : 'No hay contenidos registrados';
                                    $emptyMessage = !empty($search)
                                        ? 'Ajusta la búsqueda o límpiala para recuperar todo el listado.'
                                        : 'Aquí se mostrarán los contenidos reutilizables del sitio con su lugar, estado y accesos rápidos.';
                                    $emptyActionHref = !empty($search) ? '/contenidos' : ($canAgregar ? '/contenidos/agregar' : '');
                                    $emptyActionLabel = !empty($search) ? 'Limpiar búsqueda' : ($canAgregar ? 'Agregar contenido' : '');
                                    $emptyColspan = 5;
                                    $emptyAsTableRow = true;
                                    if (is_file($emptyView)) {
                                        include $emptyView;
                                    }
                                    ?>
                                <?php else: ?>
                                    <?php foreach ($items as $item): ?>
                                        <?php
                                        $itemId = urlencode((string) ($item['id'] ?? ''));
                                        $imagenRaw = trim((string) ($item['imagen'] ?? ''));
                                        $imagenPath = $imagenRaw !== ''
                                            ? \PanelAdmin\Core\Helpers\ImageHelper::thumb($imagenRaw, 'contenidos', '150x150')
                                            : '';
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars((string) ($item['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if ($imagenPath !== ''): ?>
                                                        <img src="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/' . $imagenPath), ENT_QUOTES, 'UTF-8') ?>"
                                                             alt="Imagen contenido"
                                                             class="rounded"
                                                             style="width:40px;height:40px;object-fit:cover;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <div><?= htmlspecialchars((string) ($item['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                                        <?php $subtitle = trim((string) ($item['subtitle'] ?? '')); ?>
                                                        <?php if ($subtitle !== ''): ?>
                                                            <?php
                                                                $subtitleWords = preg_split('/\s+/', $subtitle) ?: [];
                                                                $subtitlePreview = implode(' ', array_slice($subtitleWords, 0, 5));
                                                                if (count($subtitleWords) > 5) {
                                                                    $subtitlePreview .= '...';
                                                                }
                                                            ?>
                                                            <small class="text-muted"><?= htmlspecialchars($subtitlePreview, ENT_QUOTES, 'UTF-8') ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars((string) ($item['lugar'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
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
                                                    <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/contenidos/' . $itemId . '/editar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-warning px-2 py-1" title="Editar" aria-label="Editar">
                                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($canEliminar): ?>
                                                    <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/contenidos/' . $itemId . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-danger px-2 py-1" title="Eliminar" aria-label="Eliminar">
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
                    $paginationAriaLabel = 'Paginacion contenidos';
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
