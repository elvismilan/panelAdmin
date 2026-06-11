<div class="container-fluid">
    <?php
    $groupId = trim((string) (($user['group'] ?? '')));
        $canAgregar  = false;
        $canEditar   = false;
        $canEliminar = false;
        if ($groupId !== '') {
            try {
                $permission  = new \Core\Permission();
                $canAgregar  = $permission->canAccessRoute($groupId, '/parametros/agregar', 'agregar')   === true;
                $canEditar   = $permission->canAccessRoute($groupId, '/parametros/1/editar', 'editar')   === true;
                $canEliminar = $permission->canAccessRoute($groupId, '/parametros/1/eliminar', 'eliminar') === true;
            } catch (\Throwable) {}
        }
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Parametros del Sistema</h5>
                        <span>Listado de parametros y configuraciones</span>
                    </div>

                    <?php if ($canAgregar): ?>
                        <a href="<?= htmlspecialchars(\Core\Url::to('/parametros/agregar'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-sm">
                            <span>Agregar</span>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body pt-3">
                    <?php
                    $searchView = dirname(__DIR__) . '/components/search-form.php';
                    if (is_file($searchView)) { include $searchView; }
                    ?>

                    <?php
                    $filterBarView = dirname(__DIR__) . '/components/filter-bar.php';
                    if (is_file($filterBarView)) { include $filterBarView; }
                    ?>

                    <?php
                    $flashView = dirname(__DIR__) . '/components/flash-messages.php';
                    if (is_file($flashView)) { include $flashView; }
                    ?>

                    <?php if (!empty($pagination['totalRows'])): ?>
                        <p class="text-muted small mb-2">
                            Mostrando <?= (int) ($pagination['from'] ?? 0) ?>–<?= (int) ($pagination['to'] ?? 0) ?>
                            de <?= (int) ($pagination['totalRows'] ?? 0) ?> registros
                        </p>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>#</th>
                                    <th>Clave</th>
                                    <th>Grupo</th>
                                    <th>Etiqueta</th>
                                    <th>Tipo</th>
                                    <th class="text-center" style="width:100px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <?= !empty($search) || !empty($filters['grupo'] ?? '')
                                            ? 'No se encontraron registros para la busqueda.'
                                            : 'No hay registros.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item):
                                    $itemId = urlencode((string) ($item['par_id'] ?? ''));
                                ?>
                                    <tr>
                                        <td class="text-muted small"><?= htmlspecialchars((string) ($item['par_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><code><?= htmlspecialchars((string) ($item['par_clave'] ?? ''), ENT_QUOTES, 'UTF-8') ?></code></td>
                                        <td><?= htmlspecialchars((string) ($item['par_grupo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($item['par_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars((string) ($item['par_tipo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td class="text-center">
                                            <?php if ($canEditar): ?>
                                                <a href="<?= htmlspecialchars(\Core\Url::to('/parametros/' . $itemId . '/editar'), ENT_QUOTES, 'UTF-8') ?>"
                                                   class="btn btn-warning px-2 py-1" title="Editar">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($canEliminar): ?>
                                                <a href="<?= htmlspecialchars(\Core\Url::to('/parametros/' . $itemId . '/eliminar'), ENT_QUOTES, 'UTF-8') ?>"
                                                   class="btn btn-danger px-2 py-1" title="Eliminar">
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
                    $paginationAriaLabel = 'Paginacion parametros';
                    $paginationClass     = 'pagination justify-content-center pagination-primary pagination-sm';
                    $paginationView      = dirname(__DIR__) . '/components/pagination.php';
                    if (is_file($paginationView)) { include $paginationView; }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>