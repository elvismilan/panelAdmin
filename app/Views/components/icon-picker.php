<?php
/**
 * Icon picker component.
 * Variables esperadas:
 *   $iconActual   string  - valor actual (clase FA, puede ser vacio)
 *   $iconOptions  array   - resultado de IconHelper::options() o optionsWithSelected()
 *   $pickerId     string  - ID unico para este picker en la pagina (default: 'ele_icono')
 */
$_pickerId = $pickerId ?? 'ele_icono';
$_current = (string) ($iconActual ?? '');
$_options = $iconOptions ?? [];
$_gridId = 'icon-picker-grid-' . $_pickerId;
$_searchId = 'icon-picker-search-' . $_pickerId;
$_labelId = 'icon-picker-label-' . $_pickerId;
$_previewId = 'preview_ele_icono';
?>
<div id="grupo_icono">
    <label class="form-label">Icono</label>

    <input
        type="hidden"
        id="<?= htmlspecialchars($_pickerId, ENT_QUOTES, 'UTF-8') ?>"
        name="ele_icono"
        value="<?= htmlspecialchars($_current, ENT_QUOTES, 'UTF-8') ?>"
    >

    <div class="input-group input-group-sm mb-2">
        <span class="input-group-text"><i class="fa fa-search"></i></span>
        <input
            type="text"
            id="<?= htmlspecialchars($_searchId, ENT_QUOTES, 'UTF-8') ?>"
            class="form-control"
            placeholder="Buscar icono..."
            autocomplete="off"
        >
        <button
            type="button"
            class="btn btn-outline-secondary"
            id="<?= htmlspecialchars($_searchId, ENT_QUOTES, 'UTF-8') ?>-clear"
            title="Limpiar busqueda"
        >
            <i class="fa fa-times"></i>
        </button>
    </div>

    <div
        id="<?= htmlspecialchars($_gridId, ENT_QUOTES, 'UTF-8') ?>"
        class="border rounded p-2"
        style="max-height:200px;overflow-y:auto;display:flex;flex-wrap:wrap;gap:4px;"
    >
        <button
            type="button"
            class="icon-tile btn btn-sm <?= $_current === '' ? 'btn-primary' : 'btn-outline-secondary' ?>"
            data-value=""
            data-label="ninguno sin icono"
            title="Sin icono"
            style="width:40px;height:40px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
        >
            <i class="fa fa-ban" style="font-size:17px;"></i>
        </button>

        <?php foreach ($_options as $_opt):
            $_v = (string) ($_opt['value'] ?? '');
            $_l = (string) ($_opt['label'] ?? $_v);
            $_active = ($_current === $_v) ? 'btn-primary' : 'btn-outline-secondary';
        ?>
            <button
                type="button"
                class="icon-tile btn btn-sm <?= $_active ?>"
                data-value="<?= htmlspecialchars($_v, ENT_QUOTES, 'UTF-8') ?>"
                data-label="<?= htmlspecialchars(strtolower($_l . ' ' . $_v), ENT_QUOTES, 'UTF-8') ?>"
                title="<?= htmlspecialchars($_l, ENT_QUOTES, 'UTF-8') ?>"
                style="width:40px;height:40px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
            >
                <i class="<?= htmlspecialchars($_v, ENT_QUOTES, 'UTF-8') ?>" style="font-size:17px;"></i>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="form-text mt-1">
        Seleccionado:
        <i
            id="<?= htmlspecialchars($_previewId, ENT_QUOTES, 'UTF-8') ?>"
            class="<?= htmlspecialchars($_current !== '' ? $_current : 'fa fa-ban', ENT_QUOTES, 'UTF-8') ?> ms-1"
        ></i>
        <code id="<?= htmlspecialchars($_labelId, ENT_QUOTES, 'UTF-8') ?>" class="ms-1 text-muted small">
            <?= htmlspecialchars($_current !== '' ? $_current : 'ninguno', ENT_QUOTES, 'UTF-8') ?>
        </code>
    </div>
</div>
<script>
(function () {
    var grid = document.getElementById(<?= json_encode($_gridId) ?>);
    var search = document.getElementById(<?= json_encode($_searchId) ?>);
    var clearBtn = document.getElementById(<?= json_encode($_searchId . '-clear') ?>);
    var hidden = document.getElementById(<?= json_encode($_pickerId) ?>);
    var preview = document.getElementById(<?= json_encode($_previewId) ?>);
    var label = document.getElementById(<?= json_encode($_labelId) ?>);
    if (!grid || !search || !clearBtn || !hidden || !preview || !label) return;

    function selectTile(tile) {
        grid.querySelectorAll('.icon-tile').forEach(function (t) {
            t.classList.remove('btn-primary');
            t.classList.add('btn-outline-secondary');
        });
        tile.classList.remove('btn-outline-secondary');
        tile.classList.add('btn-primary');

        var val = tile.dataset.value || '';
        hidden.value = val;
        preview.className = (val !== '' ? val : 'fa fa-ban') + ' ms-1';
        label.textContent = val !== '' ? val : 'ninguno';
    }

    grid.querySelectorAll('.icon-tile').forEach(function (tile) {
        tile.addEventListener('click', function () { selectTile(tile); });
    });

    search.addEventListener('input', function () {
        var q = search.value.toLowerCase().trim();
        grid.querySelectorAll('.icon-tile').forEach(function (tile) {
            var match = !q || (tile.dataset.label || '').includes(q);
            tile.style.display = match ? '' : 'none';
        });
    });

    clearBtn.addEventListener('click', function () {
        search.value = '';
        grid.querySelectorAll('.icon-tile').forEach(function (t) { t.style.display = ''; });
        search.focus();
    });
})();
</script>
