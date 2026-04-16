<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Eliminar usuario</h5><span>Confirmacion de eliminacion</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>

                    <p>Esta seguro que desea eliminar al usuario
                        <strong><?= htmlspecialchars((string) ($form['usu_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                        <?php
                        $nombre = trim(((string) ($form['per_nombre'] ?? '')) . ' ' . ((string) ($form['per_apellido'] ?? '')));
                        if ($nombre !== ''): ?>
                            (<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>)
                        <?php endif; ?>?
                    </p>

                    <?php if (!empty($isSelf)): ?>
                    <div class="row justify-content-center mb-3">
                        <div class="col-sm-7">
                            <div class="alert alert-danger" role="alert">
                                <i class="fa fa-ban me-1" aria-hidden="true"></i>
                                <strong>No puedes eliminar tu propio usuario.</strong>
                            </div>
                        </div>
                    </div>
                    <?php elseif (!empty($hasLogs)): ?>
                    <div class="row justify-content-center mb-3">
                        <div class="col-sm-7">
                            <div class="alert alert-danger" role="alert">
                                <i class="fa fa-ban me-1" aria-hidden="true"></i>
                                <strong>No se puede eliminar.</strong> Este usuario tiene acciones registradas en el sistema.
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <form method="post" action="/usuarios/<?= urlencode((string) ($usuarioId ?? '')) ?>/borrar">
                        <?= $csrfField ?>
                        <button type="submit" class="btn btn-danger"
                            <?= (!empty($isSelf) || !empty($hasLogs)) ? 'disabled' : '' ?>>Eliminar</button>
                        <a href="/usuarios" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
