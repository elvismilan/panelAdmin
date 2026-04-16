<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Nueva tarea</h5><span>Agregar registro</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>
                    <form method="post" action="/tareas/guardar">
                        <?= $csrfField ?>
                        <div class="mb-3">
                            <label for="tar_nombre" class="form-label">Nombre</label>
                            <input id="tar_nombre" class="form-control" type="text" name="tar_nombre" value="<?= htmlspecialchars((string) ($form['tar_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="/tareas" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
