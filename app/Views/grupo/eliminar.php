<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Eliminar grupo</h5><span>Confirmacion de eliminacion</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }

                    $flashView = dirname(__DIR__) . '/components/flash-messages.php';
                    if (is_file($flashView)) {
                        include $flashView;
                    }
                    ?>

                    <p>Esta seguro que desea eliminar el grupo
                        <strong><?= htmlspecialchars((string) ($form['gru_descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                        (<code><?= htmlspecialchars((string) ($form['gru_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></code>)?
                    </p>

                    <?php if (!empty($hasUsuarios)): ?>
                        <div class="row justify-content-center mb-3">
                            <div class="col-sm-8">
                                <div class="alert alert-danger" role="alert">
                                    <i class="fa fa-ban me-1" aria-hidden="true"></i>
                                    <strong>No se puede eliminar.</strong>
                                    Este grupo tiene usuarios asignados. Reasigna o elimina los usuarios primero.
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="post"
                          action="<?= htmlspecialchars(\Core\Url::to('/grupos/' . urlencode((string) ($grupoId ?? '')) . '/borrar'), ENT_QUOTES, 'UTF-8') ?>">
                        <?= $csrfField ?>
                        <button type="submit"
                                class="btn btn-danger"
                                <?= !empty($hasUsuarios) ? 'disabled' : '' ?>>
                            <i class="fa fa-trash me-1" aria-hidden="true"></i>Eliminar
                        </button>
                        <a href="<?= htmlspecialchars(\Core\Url::to('/grupos'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
