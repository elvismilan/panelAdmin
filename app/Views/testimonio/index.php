<div class="container-fluid">
    <?php
    $canAgregar = $can('/testimonios/agregar', 'agregar');
    $canEditar = $can('/testimonios/1/editar', 'editar');
    $canEliminar = $can('/testimonios/1/eliminar', 'eliminar');
    $canOrdenar = $can('/testimonios/1/ordenar', 'ordenar');
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Testimonios</h5><span>Listado de testimonios</span>
                    </div>
                    <?php if ($canAgregar): ?>
                        <div class="btn-group" role="group" aria-label="Acciones de testimonios">
                            <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/testimonios/agregar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                                <span>Agregar</span>
                            </a>
                        </div>
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

                    <div class="table-responsive"
                         data-reorder-container
                         data-reorder-start="<?= (int) (is_array($pagination ?? null) ? ($pagination['offset'] ?? 0) : 0) + 1 ?>"
                         data-reorder-csrf="<?= htmlspecialchars(\PanelAdmin\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                        <table class="table table-hover align-middle">
                            <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th class="text-center">Orden</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center" style="width: 150px;">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <?= !empty($search) ? 'No se encontraron testimonios para la busqueda.' : 'No hay testimonios registrados.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php
                                $estadoLabels = ['H' => 'Habilitado', 'D' => 'Deshabilitado'];
                                $estadoBadge = ['H' => 'success', 'D' => 'secondary'];
                                ?>
                                <?php $numeroInicial = (int) (is_array($pagination ?? null) ? ($pagination['offset'] ?? 0) : 0) + 1; ?>
                                <?php foreach ($items as $posicion => $item): ?>
                                    <?php
                                    $itemId = urlencode((string) ($item['id'] ?? ''));
                                    $estado = (string) ($item['estado'] ?? '');
                                    ?>
                                    <tr data-reorder-id="<?= htmlspecialchars((string) ($item['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        <td data-row-number><?= $numeroInicial + (int) $posicion ?></td>
                                        <td>
                                            <?= htmlspecialchars((string) ($item['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            <?php if (trim((string) ($item['contexto'] ?? '')) !== ''): ?>
                                                <div class="small text-muted"><?= htmlspecialchars((string) $item['contexto'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($canOrdenar): ?>
                                                <div class="d-inline-flex align-items-center gap-1">
                                                    <button type="button"
                                                            class="btn btn-light px-2 py-1 js-reorder-btn"
                                                            data-direction="up"
                                                            data-reorder-url="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/testimonios/' . $itemId . '/ordenar'), ENT_QUOTES, 'UTF-8') ?>"
                                                            title="Subir" aria-label="Subir">
                                                        <i class="fa fa-chevron-up" aria-hidden="true"></i>
                                                    </button>
                                                    <span class="badge badge-light-primary" data-orden-value><?= (int) ($item['orden'] ?? 0) ?></span>
                                                    <button type="button"
                                                            class="btn btn-light px-2 py-1 js-reorder-btn"
                                                            data-direction="down"
                                                            data-reorder-url="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/testimonios/' . $itemId . '/ordenar'), ENT_QUOTES, 'UTF-8') ?>"
                                                            title="Bajar" aria-label="Bajar">
                                                        <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <span data-orden-value><?= (int) ($item['orden'] ?? 0) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= htmlspecialchars($estadoBadge[$estado] ?? 'secondary', ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($estadoLabels[$estado] ?? $estado, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($canEditar): ?>
                                                <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/testimonios/' . $itemId . '/editar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-warning px-2 py-1" title="Editar" aria-label="Editar">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($canEliminar): ?>
                                                <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/testimonios/' . $itemId . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-danger px-2 py-1" title="Eliminar" aria-label="Eliminar">
                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!$canEditar && !$canEliminar): ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    $paginationAriaLabel = 'Paginacion testimonios';
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
