<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Tareas</h5><span>Listado de tareas del modulo</span>
                    </div>
                    <div class="btn-group" role="group" aria-label="Acciones de tareas">
                        <a href="/admin/tareas/agregar" class="btn btn-primary btn-sm d-inline-flex align-items-center">
                            <i class="fa fa-plus me-1" aria-hidden="true"></i>
                            <span>Agregar</span>
                        </a>
                    </div>
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
                                            <a href="/admin/tareas/<?= urlencode((string) ($tarea['tar_id'] ?? '')) ?>/editar" class="btn btn-warning px-2 py-1" title="Editar" aria-label="Editar">
                                                <i class="fa fa-pencil" aria-hidden="true"></i>
                                            </a>
                                            <a href="/admin/tareas/<?= urlencode((string) ($tarea['tar_id'] ?? '')) ?>/eliminar" class="btn btn-danger px-2 py-1" title="Eliminar" aria-label="Eliminar">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                            </a>
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
