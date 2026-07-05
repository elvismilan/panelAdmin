<div class="container-fluid">
    <?php
    $groupId = trim((string) (($user['group'] ?? '')));
    $canAgregar = false;
    $canEditar = false;
    $canEliminar = false;

    if ($groupId !== '') {
        try {
            $permission = new \Core\Permission();
            $canAgregar = $permission->canAccessRoute($groupId, '/personas/agregar', 'agregar') === true;
            $canEditar = $permission->canAccessRoute($groupId, '/personas/1/editar', 'editar') === true;
            $canEliminar = $permission->canAccessRoute($groupId, '/personas/1/eliminar', 'eliminar') === true;
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
                        <h5>Personas</h5><span>Listado de personas registradas</span>
                    </div>
                    <?php if ($canAgregar): ?>
                        <div class="btn-group" role="group" aria-label="Acciones de personas">
                            <a href="<?= htmlspecialchars(\Core\Url::to('/personas/agregar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center">
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
                                <th>#</th>
                                <th>Persona</th>
                                <th>CI</th>
                                <th>Email</th>
                                <th>Telefono</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center" style="width: 120px;">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($personas)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <?= !empty($search) ? 'No se encontraron personas para la busqueda.' : 'No hay personas registradas.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($personas as $persona): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) ($persona['per_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="d-flex align-items-center gap-2">
                                            <?php $foto = (string) ($persona['per_foto'] ?? ''); ?>
                                            <?php if ($foto !== ''): ?>
                                                   <img src="<?= htmlspecialchars(\Core\Url::to('/' . ltrim((string) $foto, '/')), ENT_QUOTES, 'UTF-8') ?>"
                                                     alt="Foto"
                                                     class="rounded-circle"
                                                     style="width:36px;height:36px;object-fit:cover;">
                                            <?php else: ?>
                                                <span class="text-muted"><i class="fa fa-user-circle fa-2x" aria-hidden="true"></i></span>
                                            <?php endif; ?>
                                            <?= htmlspecialchars(
                                                trim(((string) ($persona['per_nombre'] ?? '')) . ' ' . ((string) ($persona['per_apellido'] ?? ''))),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </td>
                                        <td><?= htmlspecialchars((string) ($persona['per_ci'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($persona['per_email'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($persona['per_telefono'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-center">
                                            <?php
                                            $estado = (string) ($persona['per_estado'] ?? '');
                                            $estadoLabels = ['A' => 'Activo', 'H' => 'Habilitado', 'I' => 'Inactivo'];
                                            $estadoBadge  = ['A' => 'success', 'H' => 'success',   'I' => 'secondary'];
                                            ?>
                                            <span class="badge bg-<?= htmlspecialchars($estadoBadge[$estado] ?? 'secondary', ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($estadoLabels[$estado] ?? $estado, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php $itemId = urlencode((string) ($persona['per_id'] ?? '')); ?>
                                            <?php if ($canEditar): ?>
                                                <a href="<?= htmlspecialchars(\Core\Url::to('/personas/' . $itemId . '/editar'), ENT_QUOTES, 'UTF-8') ?>"
                                                   class="btn btn-warning px-2 py-1" title="Editar" aria-label="Editar">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($canEliminar): ?>
                                                <a href="<?= htmlspecialchars(\Core\Url::to('/personas/' . $itemId . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>"
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
                    $paginationAriaLabel = 'Paginacion personas';
                    $paginationClass     = 'pagination justify-content-center pagination-primary pagination-sm';
                    $paginationView      = dirname(__DIR__) . '/components/pagination.php';
                    if (is_file($paginationView)) {
                        include $paginationView;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
