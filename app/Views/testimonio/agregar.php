<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Nuevo testimonio</h5><span>Agregar registro</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>
                    <form method="post" action="<?= htmlspecialchars(\Core\Url::to('/testimonios/guardar'), ENT_QUOTES, 'UTF-8') ?>">
                        <?= $csrfField ?>

                        <div class="row">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input id="nombre" class="<?= htmlspecialchars($adminFieldClass('nombre'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="nombre" maxlength="<?= \App\Controllers\TestimonioController::NOMBRE_MAX ?>" placeholder="Ej.: Carla" aria-describedby="nombre-ayuda" value="<?= htmlspecialchars((string) ($form['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        <div id="nombre-ayuda" class="form-text">Solo el nombre, sin apellido.</div>
                                        <?php if ($adminFieldError('nombre') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('nombre'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="contexto" class="form-label">Contexto <span class="text-muted">(opcional)</span></label>
                                        <input id="contexto" class="<?= htmlspecialchars($adminFieldClass('contexto'), ENT_QUOTES, 'UTF-8') ?>" type="text" name="contexto" maxlength="<?= \App\Controllers\TestimonioController::CONTEXTO_MAX ?>" placeholder="Ej.: Terapia individual" aria-describedby="contexto-ayuda" value="<?= htmlspecialchars((string) ($form['contexto'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        <div id="contexto-ayuda" class="form-text">Se muestra debajo del nombre, por ejemplo "Cliente desde 2020".</div>
                                        <?php if ($adminFieldError('contexto') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('contexto'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="estado" class="form-label">Estado</label>
                                        <select id="estado" class="<?= htmlspecialchars($adminFieldClass('estado', 'form-select'), ENT_QUOTES, 'UTF-8') ?>" name="estado">
                                            <?php $estadoValue = (string) ($form['estado'] ?? 'H'); ?>
                                            <option value="H"<?= $estadoValue === 'H' ? ' selected' : '' ?>>Habilitado</option>
                                            <option value="D"<?= $estadoValue === 'D' ? ' selected' : '' ?>>Deshabilitado</option>
                                        </select>
                                        <?php if ($adminFieldError('estado') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('estado'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="detalle" class="form-label">Detalle</label>
                                <textarea id="detalle" class="<?= htmlspecialchars($adminFieldClass('detalle'), ENT_QUOTES, 'UTF-8') ?>" name="detalle" rows="5" maxlength="<?= \App\Controllers\TestimonioController::DETALLE_MAX ?>" placeholder="Texto del testimonio" aria-describedby="detalle-ayuda"><?= htmlspecialchars((string) ($form['detalle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                <div id="detalle-ayuda" class="form-text">Hasta <?= \App\Controllers\TestimonioController::DETALLE_MAX ?> caracteres. Se muestra en el carrusel de Inicio.</div>
                                <?php if ($adminFieldError('detalle') !== ''): ?><div class="invalid-feedback d-block"><?= htmlspecialchars($adminFieldError('detalle'), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="<?= htmlspecialchars(\PanelAdmin\Core\Url::to('/testimonios'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
