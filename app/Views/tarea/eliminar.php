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
                    <form method="post" action="/admin/tareas/<?= urlencode((string) ($tareaId ?? '')) ?>/borrar">
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                        <a href="/admin/tareas" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
