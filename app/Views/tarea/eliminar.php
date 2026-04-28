<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Eliminar tarea</h5><span>Confirmacion de eliminacion</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>
                    <p>Esta seguro que desea eliminar la tarea <strong><?= htmlspecialchars((string) ($form['tar_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>?</p>
                    <?php if (!empty($linkedToModulo)): ?>
                    <div class="row justify-content-start mb-3">
                        <div class="col-sm-7">
                            <div class="alert alert-danger" role="alert">
                                <i class="fa fa-ban me-1" aria-hidden="true"></i>
                                <strong>No se puede eliminar.</strong> Esta tarea esta asociada a uno o mas modulos. Desvincule la tarea de los modulos antes de continuar.
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <form method="post" action="<?= htmlspecialchars(\Core\Url::to('/tareas/' . urlencode((string) ($tareaId ?? '')) . '/borrar'), ENT_QUOTES, 'UTF-8') ?>">
                        <?= $csrfField ?>
                        <button type="submit" class="btn btn-danger" <?= !empty($linkedToModulo) ? 'disabled' : '' ?>>Eliminar</button>
                        <a href="<?= htmlspecialchars(\Core\Url::to('/tareas'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
