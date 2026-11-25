<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Eliminar galeria</h5><span>Confirmacion de eliminacion</span>
                </div>
                <div class="card-body">
                    <?php
                    $flashView = dirname(__DIR__) . '/components/flash-messages.php';
                    if (is_file($flashView)) {
                        include $flashView;
                    }
                    $totalFotos = (int) ($totalFotos ?? 0);
                    ?>
                    <p>
                        Esta seguro que desea eliminar la galeria
                        <strong><?= htmlspecialchars((string) ($form['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>?
                    </p>
                    <div class="row justify-content-start mb-3">
                        <div class="col-sm-7">
                            <div class="alert alert-warning" role="alert">
                                <i class="fa fa-exclamation-triangle me-1" aria-hidden="true"></i>
                                Esta accion no se puede deshacer.
                                <?php if ($totalFotos > 0): ?>
                                    Se eliminaran tambien sus <strong><?= $totalFotos ?></strong> foto<?= $totalFotos === 1 ? '' : 's' ?> y sus archivos.
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <form method="post" action="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/galerias/' . urlencode((string) ($itemId ?? '')) . '/borrar'), ENT_QUOTES, 'UTF-8') ?>">
                        <?= $csrfField ?>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                        <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/galerias'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
