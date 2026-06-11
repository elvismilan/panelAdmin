<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Nuevo Parametro</h5><span>Agregar registro</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) { include $errorView; }
                    ?>
                    <form method="post" action="/parametros/guardar">
                        <?= $csrfField ?>
                        <div class="mb-3">
                            <label for="par_nombre" class="form-label">Nombre</label>
                            <input id="par_nombre" class="form-control" type="text" name="par_nombre"
                                   value="<?= htmlspecialchars((string) ($form['par_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                   maxlength="250">
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="/parametros" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>