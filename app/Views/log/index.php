<div class="container-fluid">
    <?php
    $groupId = trim((string) (($user['group'] ?? '')));
    $canVer  = false;

    if ($groupId !== '') {
        try {
            $permission = new \Core\Permission();
            $canVer = $permission->canAccessRoute($groupId, '/logs/1/ver', 'ver') === true;
        } catch (\Throwable) {
            $canVer = false;
        }
    }

    $tipos = is_array($tipos ?? null) ? $tipos : [];
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Logs del sistema</h5>
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
                                    <th style="width:60px;">#</th>
                                    <th style="width:150px;">Fecha / Hora</th>
                                    <th style="width:140px;">Tipo</th>
                                    <th style="width:90px;">Usuario</th>
                                    <th>Acción</th>
                                    <th class="text-center" style="width:80px;">Ver</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <?= (!empty($search) || !empty($tipo))
                                            ? 'No se encontraron registros para los filtros aplicados.'
                                            : 'No hay logs registrados.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log):
                                    $tipoVal   = (string) ($log['log_tipo_accion'] ?? '');
                                    $tipoMeta  = $tipos[$tipoVal] ?? ['label' => $tipoVal, 'badge' => 'secondary'];
                                    $badgeClass = htmlspecialchars($tipoMeta['badge'], ENT_QUOTES, 'UTF-8');
                                    $tipoLabel  = htmlspecialchars($tipoMeta['label'], ENT_QUOTES, 'UTF-8');
                                    $accion     = (string) ($log['log_accion'] ?? '');
                                    $accionCorta = mb_strlen($accion) > 80
                                        ? mb_substr($accion, 0, 80) . '…'
                                        : $accion;
                                    $itemId = urlencode((string) ($log['log_id'] ?? ''));
                                ?>
                                    <tr>
                                        <td class="text-muted small"><?= htmlspecialchars((string) ($log['log_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="small text-nowrap">
                                            <?= htmlspecialchars((string) ($log['log_fecha'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                            <span class="text-muted"><?= htmlspecialchars((string) ($log['log_hora'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $badgeClass ?>">
                                                <?= $tipoLabel ?>
                                            </span>
                                        </td>
                                        <td class="small"><?= htmlspecialchars((string) ($log['log_usu_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="small" title="<?= htmlspecialchars($accion, ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($accionCorta, ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($canVer): ?>
                                                <a href="/logs/<?= $itemId ?>/ver"
                                                   class="btn btn-info btn-sm px-2 py-1"
                                                   title="Ver detalle"
                                                   aria-label="Ver detalle">
                                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                                </a>
                                            <?php else: ?>
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
                    $paginationAriaLabel = 'Paginacion logs';
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
