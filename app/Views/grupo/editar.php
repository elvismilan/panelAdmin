<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Editar grupo</h5><span>Actualizar registro existente</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) {
                        include $errorView;
                    }
                    ?>
                    <form method="post"
                          action="/grupos/<?= urlencode((string) ($grupoId ?? '')) ?>/actualizar"
                          autocomplete="off">
                        <?= $csrfField ?>
                        <div class="row">
                            <!-- ID del grupo (solo lectura) -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">ID de grupo</label>
                                <input class="form-control"
                                       type="text"
                                       value="<?= htmlspecialchars((string) ($form['gru_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                       readonly>
                                <div class="form-text">El ID del grupo no puede modificarse.</div>
                            </div>

                            <!-- Descripcion -->
                            <div class="col-md-6 mb-3">
                                <label for="gru_descripcion" class="form-label">Descripcion <span class="text-danger">*</span></label>
                                <input id="gru_descripcion"
                                       class="form-control"
                                       type="text"
                                       name="gru_descripcion"
                                       maxlength="250"
                                       autocomplete="off"
                                       value="<?= htmlspecialchars((string) ($form['gru_descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>

                            <!-- Estado -->
                            <div class="col-md-2 mb-3">
                                <label for="gru_estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                <select id="gru_estado" class="form-select" name="gru_estado">
                                    <option value="H" <?= ($form['gru_estado'] ?? '') === 'H' ? 'selected' : '' ?>>Activo</option>
                                    <option value="I" <?= ($form['gru_estado'] ?? '') === 'I' ? 'selected' : '' ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <!-- Permisos -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-1">
                                <i class="fa fa-key me-1" aria-hidden="true"></i>
                                Permisos del grupo
                            </h6>
                            <p class="text-muted small mb-3">
                                Selecciona los elementos y las tareas que este grupo podra ejecutar.
                            </p>

                            <?php if (empty($elementos)): ?>
                                <div class="alert alert-warning">
                                    No hay elementos disponibles para asignar permisos.
                                </div>
                            <?php else: ?>
                                <?php
                                $padres = [];
                                $hijos  = [];
                                foreach ($elementos as $el) {
                                    $padre = (int) ($el['ele_padre'] ?? 0);
                                    if ($padre === 0) {
                                        $padres[] = $el;
                                    } else {
                                        $hijos[$padre][] = $el;
                                    }
                                }
                                $permisosActivos = array_flip($permisos ?? []);
                                ?>

                                <!-- Boton global -->
                                <div class="mb-2 d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnSelAll">
                                        <i class="fa fa-check-square-o me-1" aria-hidden="true"></i>Seleccionar todo
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDeselAll">
                                        <i class="fa fa-square-o me-1" aria-hidden="true"></i>Limpiar todo
                                    </button>
                                </div>

                                <?php foreach ($padres as $padre): ?>
                                    <?php
                                    $padreId     = (int) ($padre['ele_id'] ?? 0);
                                    $hijosList   = $hijos[$padreId] ?? [];
                                    $padreTareas = $padre['tareas'] ?? [];

                                    // Union de tareas (tar_id => tar_nombre) del padre + todos sus hijos
                                    $grupTareas = [];
                                    foreach ($padreTareas as $t) {
                                        $grupTareas[(int) $t['tar_id']] = (string) $t['tar_nombre'];
                                    }
                                    foreach ($hijosList as $h) {
                                        foreach (($h['tareas'] ?? []) as $t) {
                                            $grupTareas[(int) $t['tar_id']] = (string) $t['tar_nombre'];
                                        }
                                    }
                                    if (empty($grupTareas)) { continue; }
                                    asort($grupTareas);

                                    // Indice de tareas disponibles por elemento: ele_id => [tar_id => true]
                                    $eleTaskSet = [];
                                    foreach (array_merge([$padre], $hijosList) as $el) {
                                        $eid = (int) $el['ele_id'];
                                        foreach (($el['tareas'] ?? []) as $t) {
                                            $eleTaskSet[$eid][(int) $t['tar_id']] = true;
                                        }
                                    }

                                    // Filas: primero el padre, luego los hijos (solo los que tienen tareas)
                                    $filas = [];
                                    if (!empty($eleTaskSet[$padreId])) {
                                        $filas[] = ['el' => $padre, 'nivel' => 'padre'];
                                    }
                                    foreach ($hijosList as $h) {
                                        if (!empty($eleTaskSet[(int) $h['ele_id']])) {
                                            $filas[] = ['el' => $h, 'nivel' => 'hijo'];
                                        }
                                    }
                                    if (empty($filas)) { continue; }
                                    ?>
                                    <div class="card mb-3">
                                        
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered table-hover mb-0 align-middle text-center">
                                                    <thead class="table-primary">
                                                        <tr>
                                                            <th class="text-start" style="min-width:180px">
                                                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2"
                                                                    data-grupo-sel="grupo-<?= $padreId ?>">Todos</button>
                                                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2"
                                                                        data-grupo-desel="grupo-<?= $padreId ?>">Ninguno</button>
                                                            </th>
                                                            <?php foreach ($grupTareas as $tarId => $tarNombre): ?>
                                                                <th class="fw-normal" style="min-width:80px">
                                                                    <?= htmlspecialchars(ucfirst(strtolower($tarNombre)), ENT_QUOTES, 'UTF-8') ?>
                                                                </th>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($filas as $fila): ?>
                                                            <?php
                                                            $el      = $fila['el'];
                                                            $eleId   = (int) $el['ele_id'];
                                                            $esPadre = $fila['nivel'] === 'padre';
                                                            $etiqueta = htmlspecialchars((string) ($el['ele_titulo'] ?: $el['ele_nombre']), ENT_QUOTES, 'UTF-8');
                                                            ?>
                                                            <tr <?= $esPadre ? 'class="fw-semibold"' : '' ?>>
                                                                <td class="text-start <?= $esPadre ? '' : 'ps-4 text-muted' ?>">
                                                                    <?php if (!$esPadre): ?>
                                                                        <i class="fa fa-angle-right me-1" aria-hidden="true"></i>
                                                                    <?php endif; ?>
                                                                    <?= $etiqueta ?>
                                                                </td>
                                                                <?php foreach ($grupTareas as $tarId => $tarNombre): ?>
                                                                    <td>
                                                                        <?php if (!empty($eleTaskSet[$eleId][$tarId])): ?>
                                                                            <?php $pair = $eleId . ':' . $tarId; ?>
                                                                            <div class="animate-chk d-flex justify-content-center"
                                                                                 data-grupo="grupo-<?= $padreId ?>">
                                                                                <label class="mb-0" for="p_<?= $pair ?>">
                                                                                    <input class="checkbox_animated"
                                                                                           id="p_<?= $pair ?>"
                                                                                           type="checkbox"
                                                                                           name="permisos[]"
                                                                                           value="<?= $pair ?>"
                                                                                           <?= isset($permisosActivos[$pair]) ? 'checked' : '' ?>>
                                                                                </label>
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">—</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <a href="/grupos" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var btnSel   = document.getElementById('btnSelAll');
    var btnDesel = document.getElementById('btnDeselAll');
    if (btnSel) {
        btnSel.addEventListener('click', function () {
            document.querySelectorAll('input[name="permisos[]"]').forEach(function (c) { c.checked = true; });
        });
    }
    if (btnDesel) {
        btnDesel.addEventListener('click', function () {
            document.querySelectorAll('input[name="permisos[]"]').forEach(function (c) { c.checked = false; });
        });
    }

    document.querySelectorAll('[data-grupo-sel]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var grupo = this.dataset.grupoSel;
            document.querySelectorAll('[data-grupo="' + grupo + '"] input[name="permisos[]"]').forEach(function (c) { c.checked = true; });
        });
    });
    document.querySelectorAll('[data-grupo-desel]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var grupo = this.dataset.grupoDesel;
            document.querySelectorAll('[data-grupo="' + grupo + '"] input[name="permisos[]"]').forEach(function (c) { c.checked = false; });
        });
    });
})();
</script>
