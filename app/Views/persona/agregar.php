<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Nueva persona</h5><span>Agregar registro</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>
                    <form method="post" action="<?= htmlspecialchars(\Core\Url::to('/personas/guardar'), ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data">
                        <?= $csrfField ?>
                        <div class="row">
                            <!-- Foto -->
                            <div class="col-12 mb-4">
                                <?php
                                $uploadFieldName = 'per_foto';
                                $uploadLabel     = 'Foto';
                                $uploadCurrent   = '';
                                $uploadId        = 'persona_agregar';
                                include dirname(__DIR__) . '/components/image-upload.php';
                                ?>
                            </div>
                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label for="per_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input id="per_nombre"
                                       class="form-control"
                                       type="text"
                                       name="per_nombre"
                                       maxlength="250"
                                       value="<?= htmlspecialchars((string) ($form['per_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <!-- Apellido -->
                            <div class="col-md-6 mb-3">
                                <label for="per_apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                                <input id="per_apellido"
                                       class="form-control"
                                       type="text"
                                       name="per_apellido"
                                       maxlength="250"
                                       value="<?= htmlspecialchars((string) ($form['per_apellido'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <!-- CI -->
                            <div class="col-md-4 mb-3">
                                <label for="per_ci" class="form-label">CI</label>
                                <input id="per_ci"
                                       class="form-control"
                                       type="text"
                                       name="per_ci"
                                       maxlength="255"
                                       value="<?= htmlspecialchars((string) ($form['per_ci'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <!-- Fecha de nacimiento -->
                            <div class="col-md-4 mb-3">
                                <label for="per_fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                                <input id="per_fecha_nacimiento"
                                       class="datepicker-here form-control digits"
                                       type="text"
                                       name="per_fecha_nacimiento"
                                       data-language="en"
                                       data-date-format="yyyy-mm-dd"
                                       autocomplete="off"
                                       value="<?= htmlspecialchars((string) ($form['per_fecha_nacimiento'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <!-- Sexo -->
                            <div class="col-md-4 mb-3">
                                <label for="per_sexo" class="form-label">Sexo <span class="text-danger">*</span></label>
                                <select id="per_sexo" class="form-select" name="per_sexo">
                                    <option value="M" <?= ($form['per_sexo'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                                    <option value="F" <?= ($form['per_sexo'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                                </select>
                            </div>
                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="per_email" class="form-label">Email</label>
                                <input id="per_email"
                                       class="form-control"
                                       type="text"
                                       name="per_email"
                                       maxlength="250"
                                       value="<?= htmlspecialchars((string) ($form['per_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <!-- Telefono -->
                            <div class="col-md-6 mb-3">
                                <label for="per_telefono" class="form-label">Telefono</label>
                                <input id="per_telefono"
                                       class="form-control"
                                       type="text"
                                       name="per_telefono"
                                       maxlength="50"
                                       value="<?= htmlspecialchars((string) ($form['per_telefono'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <!-- Direccion -->
                            <div class="col-md-8 mb-3">
                                <label for="per_direccion" class="form-label">Direccion</label>
                                <textarea id="per_direccion"
                                          class="form-control"
                                          name="per_direccion"
                                          rows="2"><?= htmlspecialchars((string) ($form['per_direccion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                            <!-- Estado -->
                            <div class="col-md-4 mb-3">
                                <label for="per_estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                <select id="per_estado" class="form-select" name="per_estado">
                                    <option value="H" <?= ($form['per_estado'] ?? '') === 'H' ? 'selected' : '' ?>>Activo</option>
                                    <option value="D" <?= ($form['per_estado'] ?? '') === 'D' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="<?= htmlspecialchars(\Core\Url::to('/personas'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
