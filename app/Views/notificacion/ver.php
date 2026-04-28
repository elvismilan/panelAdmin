<?php
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
$accionLabel = [
    'CREATE' => 'Creacion',
    'UPDATE' => 'Actualizacion',
    'DELETE' => 'Eliminacion',
];

$tipo    = (string) ($item['noti_tipo']   ?? 'info');
$badge   = $tipoBadge[$tipo]  ?? 'secondary';
$label   = $tipoLabel[$tipo]  ?? ucfirst($tipo);
$esLeida = (int) ($item['noti_leida'] ?? 0) === 1;
?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-sm-10 col-xl-8">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Detalle de notificacion</h5>
                        <span>Registro #<?= htmlspecialchars((string) ($item['noti_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <a href="<?= htmlspecialchars(\Core\Url::to('/notificaciones'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light btn-sm">
                        <i class="fa fa-arrow-left me-1" aria-hidden="true"></i>
                        Volver
                    </a>
                </div>
                <div class="card-body">
                    <?php
                    $flashView = dirname(__DIR__) . '/components/flash-messages.php';
                    if (is_file($flashView)) {
                        include $flashView;
                    }
                    ?>

                    <?php /* Alerta visual con el tipo de notificacion */ ?>
                    <div class="alert alert-<?= htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') ?> d-flex align-items-start gap-3 mb-4" role="alert">
                        <div class="flex-shrink-0 mt-1">
                            <?php if ($tipo === 'success'): ?>
                                <i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i>
                            <?php elseif ($tipo === 'warning'): ?>
                                <i class="fa fa-pencil-square-o fa-lg" aria-hidden="true"></i>
                            <?php elseif ($tipo === 'danger'): ?>
                                <i class="fa fa-trash fa-lg" aria-hidden="true"></i>
                            <?php else: ?>
                                <i class="fa fa-info-circle fa-lg" aria-hidden="true"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <strong><?= htmlspecialchars((string) ($item['noti_titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                            <div class="mt-1 small">
                                <?= htmlspecialchars((string) ($item['noti_mensaje'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        </div>
                    </div>

                    <dl class="row mb-0">

                        <dt class="col-sm-4 text-muted fw-normal">ID</dt>
                        <dd class="col-sm-8 font-monospace">
                            <?= htmlspecialchars((string) ($item['noti_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal">Tipo de evento</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-<?= htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal">Modulo</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-light text-dark border">
                                <?= htmlspecialchars(ucfirst((string) ($item['noti_modulo'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal">Accion</dt>
                        <dd class="col-sm-8">
                            <?php
                            $accionVal = (string) ($item['noti_accion'] ?? '');
                            echo htmlspecialchars($accionLabel[$accionVal] ?? $accionVal, ENT_QUOTES, 'UTF-8');
                            ?>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal">Ejecutado por</dt>
                        <dd class="col-sm-8">
                            <?= htmlspecialchars(trim((string) ($item['noti_usu_origen'] ?? '')) ?: '—', ENT_QUOTES, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal">Registro afectado</dt>
                        <dd class="col-sm-8 font-monospace">
                            <?= htmlspecialchars(trim((string) ($item['noti_referencia_id'] ?? '')) ?: '—', ENT_QUOTES, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal">Fecha del evento</dt>
                        <dd class="col-sm-8">
                            <?= htmlspecialchars((string) ($item['noti_fecha'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal">Estado</dt>
                        <dd class="col-sm-8">
                            <?php if ($esLeida): ?>
                                <span class="badge bg-secondary">
                                    <i class="fa fa-check me-1" aria-hidden="true"></i>Leida
                                </span>
                                <?php if (!empty($item['noti_leida_en'])): ?>
                                    <span class="text-muted small ms-2">
                                        el <?= htmlspecialchars((string) $item['noti_leida_en'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fa fa-circle me-1" aria-hidden="true"></i>No leida
                                </span>
                            <?php endif; ?>
                        </dd>

                    </dl>
                </div>
                <div class="card-footer bg-transparent d-flex gap-2">
                    <a href="<?= htmlspecialchars(\Core\Url::to('/notificaciones'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light btn-sm">
                        <i class="fa fa-arrow-left me-1" aria-hidden="true"></i>
                        Volver al listado
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
