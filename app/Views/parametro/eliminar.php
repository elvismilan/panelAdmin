<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Eliminar Parametro</h5><span>Confirmacion de eliminacion</span>
                </div>
                <div class="card-body">
                    <?php
                    $flashView = dirname(__DIR__) . '/components/flash-messages.php';
                    if (is_file($flashView)) { include $flashView; }
                    ?>
                    <p>¿Está seguro que desea eliminar
                        <strong><?= htmlspecialchars((string) ($form['par_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>?
                    </p>
                    <div class="row justify-content-start mb-3">
                        <div class="col-sm-7">
                            <div class="alert alert-warning" role="alert">
                                <i class="fa fa-exclamation-triangle me-1" aria-hidden="true"></i>
                                Esta acción no se puede deshacer.
                            </div>
                        </div>
                    </div>
                    <form method="post" action="/parametros/<?= urlencode((string) ($itemId ?? '')) ?>/borrar">
                        <?= $csrfField ?>
                        <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                        <a href="/parametros" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>