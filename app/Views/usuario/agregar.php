<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Nuevo usuario</h5><span>Agregar registro</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>
                    <form method="post" action="/usuarios/guardar" autocomplete="off">
                        <div class="row">
                            <!-- Usuario -->
                            <div class="col-md-6 mb-3">
                                <label for="usu_id" class="form-label">Usuario <span class="text-danger">*</span></label>
                                <input id="usu_id"
                                       class="form-control"
                                       type="text"
                                       name="usu_id"
                                       maxlength="250"
                                       autocomplete="off"
                                       value="<?= htmlspecialchars((string) ($form['usu_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                <div class="form-text">Nombre de usuario para iniciar sesion. No puede repetirse.</div>
                            </div>
                            <!-- Grupo -->
                            <div class="col-md-6 mb-3">
                                <label for="usu_gru_id" class="form-label">Grupo <span class="text-danger">*</span></label>
                                <select id="usu_gru_id" class="form-select" name="usu_gru_id">
                                    <option value="">-- Seleccionar grupo --</option>
                                    <?php foreach ($grupos as $grupo): ?>
                                        <option value="<?= htmlspecialchars((string) ($grupo['gru_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                            <?= ($form['usu_gru_id'] ?? '') === (string) ($grupo['gru_id'] ?? '') ? 'selected' : '' ?>>
                                            <?= htmlspecialchars((string) ($grupo['gru_descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Contrasena -->
                            <div class="col-md-6 mb-3">
                                <label for="usu_password" class="form-label">Contrasena <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input id="usu_password"
                                           class="form-control"
                                           type="password"
                                           name="usu_password"
                                           maxlength="250"
                                           autocomplete="new-password">
                                    <div class="show-hide">
                                        <span class="show" data-target="usu_password"><i class="fa fa-eye"></i></span>
                                    </div>
                                </div>
                                <div class="form-text">Minimo 6 caracteres.</div>
                            </div>
                            <!-- Confirmar contrasena -->
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirmar contrasena <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input id="confirm_password"
                                           class="form-control"
                                           type="password"
                                           name="confirm_password"
                                           maxlength="250"
                                           autocomplete="new-password">
                                    <div class="show-hide">
                                        <span class="show" data-target="confirm_password"><i class="fa fa-eye"></i></span>
                                    </div>
                                </div>
                            </div>
                            <!-- Persona -->
                            <div class="col-md-8 mb-3">
                                <label for="usu_per_id" class="form-label">Persona</label>
                                <select id="usu_per_id" class="form-select" name="usu_per_id">
                                    <option value="">-- Seleccionar persona --</option>
                                    <?php foreach ($personas as $persona): ?>
                                        <option value="<?= htmlspecialchars((string) ($persona['per_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                            <?= (string) ($form['usu_per_id'] ?? '') === (string) ($persona['per_id'] ?? '') ? 'selected' : '' ?>>
                                            <?= htmlspecialchars(
                                                trim(((string) ($persona['per_apellido'] ?? '')) . ', ' . ((string) ($persona['per_nombre'] ?? ''))),
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Solo se muestran personas sin usuario asignado.</div>
                            </div>
                            <!-- Estado -->
                            <div class="col-md-4 mb-3">
                                <label for="usu_estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                <select id="usu_estado" class="form-select" name="usu_estado">
                                    <option value="H" <?= ($form['usu_estado'] ?? '') === 'H' ? 'selected' : '' ?>>Activo</option>
                                    <option value="I" <?= ($form['usu_estado'] ?? '') === 'I' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="/usuarios" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
