<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Editar modulo</h5><span>Actualizar registro existente</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>
                    <form method="post" action="/admin/modulos/<?= urlencode((string) ($elementoId ?? '')) ?>/actualizar">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <div class="row g-2">
                                    <div class="col-md-6 mb-3">
                                        <label for="ele_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                        <input id="ele_nombre" class="form-control" type="text" name="ele_nombre"
                                            maxlength="250"
                                            value="<?= htmlspecialchars((string) ($form['ele_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="ele_titulo" class="form-label">Slug <span class="text-danger">*</span></label>
                                        <input id="ele_titulo" class="form-control" type="text" name="ele_titulo"
                                            maxlength="100"
                                            value="<?= htmlspecialchars((string) ($form['ele_titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6 mb-3">
                                        <label for="ele_tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                                        <select id="ele_tipo" class="form-select" name="ele_tipo">
                                            <?php
                                            $tipos = ['M' => 'Menu', 'S' => 'Submenu', 'A' => 'Accion'];
                                            $tipoActual = (string) ($form['ele_tipo'] ?? 'M');
                                            foreach ($tipos as $val => $label):
                                            ?>
                                                <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"
                                                    <?= $tipoActual === $val ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="mb-3" id="grupo_padre">
                                            <label for="ele_padre" class="form-label">Modulo padre</label>
                                            <select id="ele_padre" class="form-select" name="ele_padre">
                                                <option value="">-- Sin padre --</option>
                                                <?php
                                                $padreActual = (string) ($form['ele_padre'] ?? '');
                                                $elId = (string) ($elementoId ?? '');
                                                foreach (($padres ?? []) as $padre):
                                                    $padreId = (string) ($padre['ele_id'] ?? '');
                                                    if ($padreId === $elId) continue;
                                                ?>
                                                    <option value="<?= htmlspecialchars($padreId, ENT_QUOTES, 'UTF-8') ?>"
                                                        <?= $padreActual === $padreId ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars((string) ($padre['ele_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6 mb-3">
                                        <label for="ele_orden" class="form-label">Orden</label>
                                        <input id="ele_orden" class="form-control" type="number" name="ele_orden"
                                            min="0"
                                            value="<?= htmlspecialchars((string) ($form['ele_orden'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div id="grupo_icono">
                                            <label for="ele_icono" class="form-label">Icono <small class="text-muted">(clase CSS, ej: fa fa-home)</small></label>
                                            <input id="ele_icono" class="form-control" type="text" name="ele_icono"
                                                maxlength="250"
                                                value="<?= htmlspecialchars((string) ($form['ele_icono'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6 mb-3">
                                        <label for="ele_estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                        <select id="ele_estado" class="form-select" name="ele_estado">
                                            <?php
                                            $estados = ['H' => 'Habilitado', 'I' => 'Inhabilitado'];
                                            $estadoActual = (string) ($form['ele_estado'] ?? 'H');
                                            foreach ($estados as $val => $label):
                                            ?>
                                                <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"
                                                    <?= $estadoActual === $val ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="ele_tarea" class="form-label">Tarea por defecto <span class="text-danger">*</span></label>
                                        <input id="ele_tarea" class="form-control" type="text" name="ele_tarea"
                                            maxlength="100"
                                            value="<?= htmlspecialchars((string) ($form['ele_tarea'] ?? 'ACCEDER'), ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label d-block">Tareas</label>
                                <div class="table-responsive" style="max-height: 430px; overflow-y: auto;">
                                    <table class="table table-sm table-bordered table-hover align-middle mb-0">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>
                                                    <div class="checkbox checkbox-solid-primary mb-0">
                                                        <input type="checkbox" id="checkAllTareas">
                                                        <label class="mb-0" for="checkAllTareas">Tarea</label>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($todasTareas)): ?>
                                                <tr><td class="text-center text-muted">Sin tareas disponibles</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($todasTareas as $tarea):
                                                    $tarId = (string) ($tarea['tar_id'] ?? '');
                                                    $checked = in_array($tarId, array_map('strval', $tareasSeleccionadas ?? []), true);
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <div class="checkbox checkbox-solid-primary mb-0">
                                                                <input class="tarea-check" type="checkbox"
                                                                       name="tareas[]"
                                                                       value="<?= htmlspecialchars($tarId, ENT_QUOTES, 'UTF-8') ?>"
                                                                       id="tarea_<?= htmlspecialchars($tarId, ENT_QUOTES, 'UTF-8') ?>"
                                                                       <?= $checked ? 'checked' : '' ?>>
                                                                <label class="mb-0" for="tarea_<?= htmlspecialchars($tarId, ENT_QUOTES, 'UTF-8') ?>">
                                                                    <?= htmlspecialchars((string) ($tarea['tar_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                                </label>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                            <a href="/admin/modulos" class="btn btn-light">Cancelar</a>
                        </div>
                    </form>
                    <script>
                        (function () {
                            var tipoSelect = document.getElementById('ele_tipo');
                            var grupoIcono = document.getElementById('grupo_icono');
                            var grupoPadre = document.getElementById('grupo_padre');
                            function toggleExtras() {
                                var show = tipoSelect.value === 'M';
                                grupoIcono.style.display = show ? '' : 'none';
                                grupoPadre.style.display = show ? '' : 'none';
                            }
                            tipoSelect.addEventListener('change', toggleExtras);
                            toggleExtras();
                            var checkAll = document.getElementById('checkAllTareas');
                            checkAll.addEventListener('change', function () {
                                document.querySelectorAll('.tarea-check').forEach(function (c) {
                                    c.checked = checkAll.checked;
                                });
                            });
                        })();
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
