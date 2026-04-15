<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Eliminar persona</h5><span>Confirmacion de eliminacion</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>

                    <p>Esta seguro que desea eliminar a
                        <strong><?= htmlspecialchars(
                            trim(
                                ((string) ($form['per_nombre'] ?? '')) . ' ' .
                                ((string) ($form['per_apellido'] ?? ''))
                            ),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?></strong>?
                    </p>

                    <?php if (!empty($linkedToUsuario)): ?>
                    <div class="row justify-content-start mb-3">
                        <div class="col-sm-7">
                            <div class="alert alert-danger" role="alert">
                                <i class="fa fa-ban me-1" aria-hidden="true"></i>
                                <strong>No se puede eliminar.</strong> Esta persona tiene un usuario del sistema asociado.
                                Elimine primero el usuario antes de continuar.
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <form method="post" action="/personas/<?= urlencode((string) ($personaId ?? '')) ?>/borrar">
                        <?= $csrfField ?>
                        <button type="submit" class="btn btn-danger" <?= !empty($linkedToUsuario) ? 'disabled' : '' ?>>Eliminar</button>
                        <a href="/personas" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
