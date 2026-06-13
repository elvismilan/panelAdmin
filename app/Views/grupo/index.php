<div class="container-fluid">
    <?php
    $groupId = trim((string) (($user['group'] ?? '')));
    $canAgregar = false;
    $canEditar = false;
    $canEliminar = false;

    if ($groupId !== '') {
        try {
            $permission = new \Core\Permission();
            $canAgregar = $permission->canAccessRoute($groupId, '/grupos/agregar', 'agregar') === true;
            $canEditar = $permission->canAccessRoute($groupId, '/grupos/1/editar', 'editar') === true;
            $canEliminar = $permission->canAccessRoute($groupId, '/grupos/1/eliminar', 'eliminar') === true;
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
                        <h5>Grupos</h5><span>Listado de grupos y sus permisos</span>
                    </div>
                    <?php if ($canAgregar): ?>
                        <div class="btn-group" role="group" aria-label="Acciones de grupos">
                            <a href="<?= htmlspecialchars(\Core\Url::to('/grupos/agregar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center">
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
                        'filterBarGroups' => [],
                    ];
                    $toolbarView = dirname(__DIR__) . '/components/list-toolbar.php';
                    if (is_file($toolbarView)) {
                        include $toolbarView;
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
                                <th>ID</th>
                                <th>Descripcion</th>
                                <th class="text-center">Usuarios</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center" style="width: 120px;">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($grupos)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <?= !empty($search) ? 'No se encontraron grupos para la busqueda.' : 'No hay grupos registrados.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($grupos as $grupo): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars((string) ($grupo['gru_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></code></td>
                                        <td><?= htmlspecialchars((string) ($grupo['gru_descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-info text-dark">
                                                <?= (int) ($grupo['total_usuarios'] ?? 0) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $estado      = (string) ($grupo['gru_estado'] ?? '');
                                            $estadoLabel = ['H' => 'Activo', 'I' => 'Inactivo'];
                                            $estadoBadge = ['H' => 'success', 'I' => 'secondary'];
                                            ?>
                                            <span class="badge bg-<?= htmlspecialchars($estadoBadge[$estado] ?? 'secondary', ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($estadoLabel[$estado] ?? $estado, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php $encId = urlencode((string) ($grupo['gru_id'] ?? '')); ?>
                                            <?php if ($canEditar): ?>
                                                <a href="<?= htmlspecialchars(\Core\Url::to('/grupos/' . $encId . '/editar'), ENT_QUOTES, 'UTF-8') ?>"
                                                   class="btn btn-warning px-2 py-1" title="Editar" aria-label="Editar">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($canEliminar): ?>
                                                <a href="<?= htmlspecialchars(\Core\Url::to('/grupos/' . $encId . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>"
                                                   class="btn btn-danger px-2 py-1" title="Eliminar" aria-label="Eliminar">
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
