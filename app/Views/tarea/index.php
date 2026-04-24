<div class="container-fluid">
    <?php
    $groupId = trim((string) (($user['group'] ?? '')));
    $canAgregar = false;
    $canEditar = false;
    $canEliminar = false;

    if ($groupId !== '') {
        try {
            $permission = new \Core\Permission();
            $canAgregar = $permission->canAccessRoute($groupId, '/tareas/agregar', 'agregar') === true;
            $canEditar = $permission->canAccessRoute($groupId, '/tareas/1/editar', 'editar') === true;
            $canEliminar = $permission->canAccessRoute($groupId, '/tareas/1/eliminar', 'eliminar') === true;
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
                        <h5>Tareas</h5><span>Listado de tareas del modulo</span>
                    </div>
                    <?php if ($canAgregar): ?>
                        <div class="btn-group" role="group" aria-label="Acciones de tareas">
                            <a href="<?= htmlspecialchars(\Core\Url::to('/tareas/agregar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center">
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
                                <th class="text-center" style="width: 150px;">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($tareas)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">
                                        <?= !empty($search) ? 'No se encontraron tareas para la busqueda.' : 'No hay tareas registradas.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tareas as $tarea): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) ($tarea['tar_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($tarea['tar_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-center">
                                            <?php $itemId = urlencode((string) ($tarea['tar_id'] ?? '')); ?>
                                            <?php if ($canEditar): ?>
                                                <a href="<?= htmlspecialchars(\Core\Url::to('/tareas/' . $itemId . '/editar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-warning px-2 py-1" title="Editar" aria-label="Editar">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($canEliminar): ?>
                                                <a href="<?= htmlspecialchars(\Core\Url::to('/tareas/' . $itemId . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-danger px-2 py-1" title="Eliminar" aria-label="Eliminar">
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
                    $paginationAriaLabel = 'Paginacion tareas';
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
