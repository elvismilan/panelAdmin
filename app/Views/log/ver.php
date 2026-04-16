<div class="container-fluid">
    <?php
    $tipos    = is_array($tipos ?? null) ? $tipos : [];
    $log      = is_array($log ?? null) ? $log : [];
    $tipoVal  = (string) ($log['log_tipo_accion'] ?? '');
    $tipoMeta = $tipos[$tipoVal] ?? ['label' => $tipoVal !== '' ? $tipoVal : 'Desconocido', 'badge' => 'secondary'];
    ?>
    <div class="row justify-content-center">
        <div class="col-sm-10 col-xl-9">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Detalle del log</h5>
                        <span>Registro #<?= htmlspecialchars((string) ($log['log_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <a href="/logs" class="btn btn-light btn-sm">
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

                    <dl class="row mb-0">

                        <dt class="col-sm-4 text-muted fw-normal">ID</dt>
                        <dd class="col-sm-8 font-monospace">
                            <?= htmlspecialchars((string) ($log['log_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal">Tipo</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-<?= htmlspecialchars($tipoMeta['badge'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($tipoMeta['label'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span class="text-muted small ms-1">(<?= htmlspecialchars($tipoVal, ENT_QUOTES, 'UTF-8') ?>)</span>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal">Fecha</dt>
                        <dd class="col-sm-8">
                            <?= htmlspecialchars((string) ($log['log_fecha'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal">Hora</dt>
                        <dd class="col-sm-8">
                            <?= htmlspecialchars((string) ($log['log_hora'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal">Usuario</dt>
                        <dd class="col-sm-8">
                            <?= htmlspecialchars((string) ($log['log_usu_id'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal">IP</dt>
                        <dd class="col-sm-8 font-monospace">
                            <?= htmlspecialchars((string) ($log['log_ip'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal">Host</dt>
                        <dd class="col-sm-8 font-monospace">
                            <?= htmlspecialchars((string) ($log['log_pc'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                        </dd>

                        <dt class="col-sm-4 text-muted fw-normal">Acción</dt>
                        <dd class="col-sm-8">
                            <div class="bg-dark rounded p-3 small font-monospace"
                                 style="white-space: pre-wrap; word-break: break-word;"><?= htmlspecialchars(trim((string) ($log['log_accion'] ?? '')) ?: '—', ENT_QUOTES, 'UTF-8') ?></div>
                        </dd>

                    </dl>

                </div>
            </div>
        </div>
    </div>
</div>
