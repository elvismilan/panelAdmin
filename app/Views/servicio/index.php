<div class="container-fluid">
    <?php
    $canAgregar = $can('/servicios/agregar', 'agregar');
    $canEditar = $can('/servicios/1/editar', 'editar');
    $canEliminar = $can('/servicios/1/eliminar', 'eliminar');
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Servicios</h5><span>Listado de servicios</span>
                    </div>
                    <?php if ($canAgregar): ?>
                        <div class="btn-group" role="group" aria-label="Acciones de servicios">
                            <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/servicios/agregar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center">
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

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Servicio</th>
                                <th class="text-center">Icono</th>
                                <th class="text-center">Vistas</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center" style="width: 150px;">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <?= !empty($search) ? 'No se encontraron servicios para la busqueda.' : 'No hay servicios registrados.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php
                                $estadoLabels = ['H' => 'Habilitado', 'D' => 'Deshabilitado'];
                                $estadoBadge = ['H' => 'success', 'D' => 'secondary'];
                                ?>
                                <?php foreach ($items as $item): ?>
                                    <?php
                                    $itemId = urlencode((string) ($item['id'] ?? ''));
                                    $estado = (string) ($item['estado'] ?? '');
                                    $imagenRaw = trim((string) ($item['imagen'] ?? ''));
                                    $imagenPath = $imagenRaw !== ''
                                        ? \PanelAdmin\Core\Helpers\ImageHelper::thumb($imagenRaw, 'servicios', '150x150')
                                        : '';
                                    $iconRaw = trim((string) ($item['icon'] ?? ''));
                                    $iconPath = $iconRaw !== ''
                                        ? \PanelAdmin\Core\Helpers\ImageHelper::original($iconRaw, 'servicios')
                                        : '';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) ($item['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php if ($imagenPath !== ''): ?>
                                                    <img src="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/' . $imagenPath), ENT_QUOTES, 'UTF-8') ?>"
                                                         alt="<?= htmlspecialchars((string) ($item['titulo'] ?? 'Imagen'), ENT_QUOTES, 'UTF-8') ?>"
                                                         class="rounded flex-shrink-0"
                                                         style="width:40px;height:40px;object-fit:cover;">
                                                <?php else: ?>
                                                    <span class="text-muted flex-shrink-0" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">—</span>
                                                <?php endif; ?>
                                                <span><?= htmlspecialchars((string) ($item['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($iconPath !== ''): ?>
                                                <img src="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/' . $iconPath), ENT_QUOTES, 'UTF-8') ?>"
                                                     alt="Icono"
                                                     style="width:28px;height:28px;object-fit:contain;">
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= (int) ($item['vistas'] ?? 0) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= htmlspecialchars($estadoBadge[$estado] ?? 'secondary', ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($estadoLabels[$estado] ?? $estado, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($canEditar): ?>
                                                <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/servicios/' . $itemId . '/editar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-warning px-2 py-1" title="Editar" aria-label="Editar">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($canEliminar): ?>
                                                <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/servicios/' . $itemId . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-danger px-2 py-1" title="Eliminar" aria-label="Eliminar">
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
                    $paginationAriaLabel = 'Paginacion servicios';
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
