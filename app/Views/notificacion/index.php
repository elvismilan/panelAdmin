<div class="container-fluid">
    <?php
    // Notificaciones es un modulo interno sin registro en `elemento`.
    // El acceso ya se resuelve por autenticacion y bypass interno.
    $canVer = true;

    $tipoBadge = [
        'success' => 'success',
        'warning' => 'warning',
        'danger'  => 'danger',
        'info'    => 'info',
    ];
    $tipoLabel = [
        'success' => 'Creado',
        'warning' => 'Actualizado',
        'danger'  => 'Eliminado',
        'info'    => 'Info',
    ];
    $moduloActual = (string) ($modulo ?? '');
    $leidaActual  = (string) ($leida  ?? '');
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Notificaciones</h5>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <?php
                    $searchView = dirname(__DIR__) . '/components/search-form.php';
                    if (is_file($searchView)) {
                        include $searchView;
                    }
                    ?>

                    <?php /* Filtros adicionales: modulo y estado leida */ ?>
                    <form method="get" action="<?= htmlspecialchars(\Core\Url::to('/notificaciones'), ENT_QUOTES, 'UTF-8') ?>" class="row g-2 mb-3">
                        <?php if (!empty($search)): ?>
                            <input type="hidden" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                        <?php endif; ?>
                        <div class="col-auto">
                            <select name="modulo" class="form-select form-select-sm"
                                    onchange="this.form.submit()" aria-label="Filtrar por modulo">
                                <option value="">Todos los modulos</option>
                                <?php foreach (['usuarios' => 'Usuarios', 'personas' => 'Personas', 'grupos' => 'Grupos', 'modulos' => 'Modulos', 'tareas' => 'Tareas'] as $val => $label): ?>
                                    <option value="<?= $val ?>"
                                        <?= $moduloActual === $val ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="leida" class="form-select form-select-sm"
                                    onchange="this.form.submit()" aria-label="Filtrar por estado">
                                <option value="">Todas</option>
                                <option value="0" <?= $leidaActual === '0' ? 'selected' : '' ?>>No leidas</option>
                                <option value="1" <?= $leidaActual === '1' ? 'selected' : '' ?>>Leidas</option>
                            </select>
                        </div>
                        <?php if ($moduloActual !== '' || $leidaActual !== ''): ?>
                            <div class="col-auto">
                                <a href="<?= htmlspecialchars(\Core\Url::to('/notificaciones' . ($search !== '' ? '?q=' . urlencode($search) : '')), ENT_QUOTES, 'UTF-8') ?>"
                                   class="btn btn-outline-secondary btn-sm">
                                    Limpiar filtros
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>

                    <?php
                    $flashView = dirname(__DIR__) . '/components/flash-messages.php';
                    if (is_file($flashView)) {
                        include $flashView;
                    }
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
                                    <th style="width:55px;">#</th>
                                    <th style="width:80px;">Tipo</th>
                                    <th>Titulo</th>
                                    <th style="width:100px;">Modulo</th>
                                    <th style="width:90px;">Usuario</th>
                                    <th style="width:150px;">Fecha</th>
                                    <th style="width:80px;" class="text-center">Estado</th>
                                    <th style="width:70px;" class="text-center">Ver</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <?= (!empty($search) || $moduloActual !== '' || $leidaActual !== '')
                                            ? 'No se encontraron notificaciones para los filtros aplicados.'
                                            : 'No hay notificaciones registradas.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $item):
                                    $tipo       = (string) ($item['noti_tipo'] ?? 'info');
                                    $badge      = $tipoBadge[$tipo]  ?? 'secondary';
                                    $label      = $tipoLabel[$tipo]  ?? ucfirst($tipo);
                                    $esLeida    = (int) ($item['noti_leida'] ?? 0) === 1;
                                    $rowClass   = $esLeida ? '' : 'fw-semibold';
                                    $itemId     = urlencode((string) ($item['noti_id'] ?? ''));
                                    $titulo     = (string) ($item['noti_titulo'] ?? '');
                                    $tituloCorto = mb_strlen($titulo) > 60
                                        ? mb_substr($titulo, 0, 60) . '…'
                                        : $titulo;
                                ?>
                                    <tr class="<?= $rowClass ?>">
                                        <td class="text-muted small"><?= htmlspecialchars((string) ($item['noti_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <span class="badge bg-<?= htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td title="<?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?>">
                                            <?php if (!$esLeida): ?>
                                                <span class="badge bg-primary me-1" style="font-size:.65em;">Nuevo</span>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($tituloCorto, ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars(ucfirst((string) ($item['noti_modulo'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="small"><?= htmlspecialchars((string) ($item['noti_usu_origen'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="small text-nowrap">
                                            <?= htmlspecialchars((string) ($item['noti_fecha'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($esLeida): ?>
                                                <span class="text-muted small" title="Leida">
                                                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-warning small" title="No leida">
                                                    <i class="fa fa-circle" aria-hidden="true"></i>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($canVer): ?>
                                                                <a href="<?= htmlspecialchars(\Core\Url::to('/notificaciones/' . $itemId . '/ver'), ENT_QUOTES, 'UTF-8') ?>"
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
                    $paginationAriaLabel = 'Paginacion notificaciones';
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
