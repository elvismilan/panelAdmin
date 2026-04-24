<div class="container-fluid">
    <?php
    $groupId = trim((string) (($user['group'] ?? '')));
    $canAgregar = false;
    $canEditar = false;
    $canEliminar = false;

    if ($groupId !== '') {
        try {
            $permission = new \Core\Permission();
            $canAgregar = $permission->canAccessRoute($groupId, '/modulos/agregar', 'agregar') === true;
            $canEditar = $permission->canAccessRoute($groupId, '/modulos/1/editar', 'editar') === true;
            $canEliminar = $permission->canAccessRoute($groupId, '/modulos/1/eliminar', 'eliminar') === true;
        } catch (\Throwable) {
            $canAgregar = false;
            $canEditar = false;
            $canEliminar = false;
        }
    }
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Modulos</h5><span>Listado de modulos del sistema</span>
                    </div>
                    <?php if ($canAgregar): ?>
                        <div class="btn-group" role="group" aria-label="Acciones de modulos">
                            <a href="<?= htmlspecialchars(\Core\Url::to('/modulos/agregar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                               <span>Agregar</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body pt-3">
                    <?php
                    $searchView = dirname(__DIR__) . '/components/search-form.php';
                    if (is_file($searchView)) {
                        include $searchView;
                    }
                    ?>

                    <?php
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
                                <th>Slug</th>
                                <th>Estado</th>
                                <th class="text-center" style="width: 150px;">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($elementos)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <?= !empty($search) ? 'No se encontraron modulos para la busqueda.' : 'No hay modulos registrados.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php
                                $estadoLabels = ['H' => 'Habilitado', 'I' => 'Inhabilitado'];
                                $estadoBadge  = ['H' => 'success', 'I' => 'secondary'];
                                ?>
                                <?php foreach ($elementos as $elemento): ?>
                                    <?php
                                    $estado = (string) ($elemento['ele_estado'] ?? '');
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) ($elemento['ele_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($elemento['ele_titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($elemento['ele_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <span class="badge bg-<?= htmlspecialchars($estadoBadge[$estado] ?? 'secondary', ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($estadoLabels[$estado] ?? $estado, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php $itemId = urlencode((string) ($elemento['ele_id'] ?? '')); ?>
                                            <?php if ($canEditar): ?>
                                                <a href="<?= htmlspecialchars(\Core\Url::to('/modulos/' . $itemId . '/editar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-warning px-2 py-1" title="Editar" aria-label="Editar">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($canEliminar): ?>
                                                <a href="<?= htmlspecialchars(\Core\Url::to('/modulos/' . $itemId . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-danger px-2 py-1" title="Eliminar" aria-label="Eliminar">
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
                    $paginationAriaLabel = 'Paginacion modulos';
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
