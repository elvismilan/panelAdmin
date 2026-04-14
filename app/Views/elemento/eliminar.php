<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Eliminar modulo</h5><span>Confirmacion de eliminacion</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>
                    <p>Esta seguro que desea eliminar el modulo <strong><?= htmlspecialchars((string) ($form['ele_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>?</p>
                    <?php if (empty($linkedToPermiso)): ?>
                    <div class="row justify-content-start mb-3">
                        <div class="col-sm-7">
                            <div class="alert alert-warning" role="alert">
                                <i class="fa fa-exclamation-triangle me-1" aria-hidden="true"></i>
                                Se eliminaran tambien todas las tareas asociadas a este modulo.
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($linkedToPermiso)): ?>
                    <div class="row justify-content-start mb-3">
                        <div class="col-sm-7">
                            <div class="alert alert-danger" role="alert">
                                <i class="fa fa-ban me-1" aria-hidden="true"></i>
                                <strong>No se puede eliminar.</strong> Este modulo tiene permisos asignados en el sistema. Elimine primero los permisos asociados antes de continuar.
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <form method="post" action="/admin/modulos/<?= urlencode((string) ($elementoId ?? '')) ?>/borrar">
                        <button type="submit" class="btn btn-danger" <?= !empty($linkedToPermiso) ? 'disabled' : '' ?>>Eliminar</button>
                        <a href="/admin/modulos" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
