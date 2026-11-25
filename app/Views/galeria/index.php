<div class="container-fluid">
    <?php
    $canAgregar = $can('/galerias/agregar', 'agregar');
    $canEditar = $can('/galerias/1/editar', 'editar');
    $canEliminar = $can('/galerias/1/eliminar', 'eliminar');
    $canFotos = $can('/galerias/1/fotos', 'fotos');
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Galerias</h5><span>Listado de galerias de fotos</span>
                    </div>
                    <?php if ($canAgregar): ?>
                        <div class="btn-group" role="group" aria-label="Acciones de galerias">
                            <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/galerias/agregar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center">
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
                                <th>Nombre</th>
                                <th>Fecha</th>
                                <th class="text-center">Fotos</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center" style="width: 170px;">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <?= !empty($search) ? 'No se encontraron galerias para la busqueda.' : 'No hay galerias registradas.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php
                                $estadoLabels = ['H' => 'Habilitada', 'D' => 'Deshabilitada', 'E' => 'Eliminada'];
                                $estadoBadge = ['H' => 'success', 'D' => 'secondary', 'E' => 'danger'];
                                ?>
                                <?php foreach ($items as $item): ?>
                                    <?php
                                    $itemId = urlencode((string) ($item['id'] ?? ''));
                                    $estado = (string) ($item['estado'] ?? '');
                                    $portadaRaw = trim((string) ($item['portada'] ?? ''));
                                    $portadaPath = $portadaRaw !== ''
                                        ? \PanelAdmin\Core\Helpers\ImageHelper::thumb($portadaRaw, 'galeria', '150x150')
                                        : '';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) ($item['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="d-flex align-items-center gap-2">
                                            <?php if ($portadaPath !== ''): ?>
                                                <img src="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/' . $portadaPath), ENT_QUOTES, 'UTF-8') ?>"
                                                     alt="<?= htmlspecialchars((string) ($item['nombre'] ?? 'Portada'), ENT_QUOTES, 'UTF-8') ?>"
                                                     class="rounded"
                                                     style="width:40px;height:40px;object-fit:cover;">
                                            <?php else: ?>
                                                <span class="rounded bg-light text-muted d-inline-flex align-items-center justify-content-center"
                                                      style="width:40px;height:40px;">
                                                    <i class="fa fa-picture-o" aria-hidden="true"></i>
                                                </span>
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars((string) ($item['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                        </td>
                                        <td><?= htmlspecialchars((string) ($item['fecha'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-center">
                                            <span class="badge badge-light-primary"><?= (int) ($item['total_fotos'] ?? 0) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= htmlspecialchars($estadoBadge[$estado] ?? 'secondary', ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($estadoLabels[$estado] ?? $estado, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($canFotos): ?>
                                                <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/galerias/' . $itemId . '/fotos'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary px-2 py-1" title="Fotos" aria-label="Fotos">
                                                    <i class="fa fa-picture-o" aria-hidden="true"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($canEditar): ?>
                                                <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/galerias/' . $itemId . '/editar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-warning px-2 py-1" title="Editar" aria-label="Editar">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($canEliminar): ?>
                                                <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/galerias/' . $itemId . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-danger px-2 py-1" title="Eliminar" aria-label="Eliminar">
                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!$canFotos && !$canEditar && !$canEliminar): ?>
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
                    $paginationAriaLabel = 'Paginacion galerias';
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
