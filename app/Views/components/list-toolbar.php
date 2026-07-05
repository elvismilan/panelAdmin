<?php
$listToolbarConfig = is_array($listToolbarConfig ?? null) ? $listToolbarConfig : [];
$mode = strtolower((string) ($listToolbarConfig['mode'] ?? 'simple'));

$renderSearchToolbar = static function (
    string $action,
    string $method,
    string $searchName,
    string $searchValue,
    string $searchPlaceholder,
    string $submitLabel,
    string $submitIcon,
    string $searchGroupStyle,
    string $buttonClass,
    string $clearUrl = '',
    string $clearLabel = 'Limpiar',
    array $hiddenFields = [],
    array $sideButtons = []
): void {
    if ($action === '' || $searchName === '') {
        return;
    }
    ?>
    <form method="<?= htmlspecialchars($method, ENT_QUOTES, 'UTF-8') ?>" action="<?= htmlspecialchars(\Core\Url::to($action), ENT_QUOTES, 'UTF-8') ?>" class="list-toolbar-search-form mb-3">
        <div class="list-toolbar-search-row d-flex flex-wrap justify-content-center align-items-center gap-2 mb-2">
            <div class="input-group input-group-sm list-toolbar-search-group" style="<?= htmlspecialchars($searchGroupStyle, ENT_QUOTES, 'UTF-8') ?>">
                <input
                    type="text"
                    name="<?= htmlspecialchars($searchName, ENT_QUOTES, 'UTF-8') ?>"
                    class="form-control list-toolbar-search-input"
                    placeholder="<?= htmlspecialchars($searchPlaceholder, ENT_QUOTES, 'UTF-8') ?>"
                    value="<?= htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8') ?>"
                >
                <?php foreach ($hiddenFields as $hiddenField): ?>
                    <?php
                    $hiddenField = is_array($hiddenField) ? $hiddenField : [];
                    $hiddenName = (string) ($hiddenField['name'] ?? '');
                    $hiddenValue = (string) ($hiddenField['value'] ?? '');
                    ?>
                    <?php if ($hiddenName !== '' && $hiddenValue !== ''): ?>
                        <input type="hidden" name="<?= htmlspecialchars($hiddenName, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($hiddenValue, ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-outline-primary list-toolbar-search-btn <?= htmlspecialchars($buttonClass, ENT_QUOTES, 'UTF-8') ?>">
                    <i class="<?= htmlspecialchars($submitIcon, ENT_QUOTES, 'UTF-8') ?> me-1" aria-hidden="true"></i><?= htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8') ?>
                </button>
            </div>

            <?php if ($clearUrl !== ''): ?>
                <a href="<?= htmlspecialchars(\Core\Url::to($clearUrl), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary btn-sm list-toolbar-clear-btn <?= htmlspecialchars($buttonClass, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($clearLabel, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endif; ?>

            <?php foreach ($sideButtons as $sideButton): ?>
                <?php
                $sideButton = is_array($sideButton) ? $sideButton : [];
                $tag = strtolower((string) ($sideButton['tag'] ?? 'button'));
                $label = (string) ($sideButton['label'] ?? '');
                $icon = (string) ($sideButton['icon'] ?? '');
                $class = (string) ($sideButton['class'] ?? '');
                $attrs = is_array($sideButton['attrs'] ?? null) ? $sideButton['attrs'] : [];
                ?>
                <?php if ($tag === 'a'): ?>
                    <a
                        <?php foreach ($attrs as $attrName => $attrValue): ?>
                            <?= htmlspecialchars((string) $attrName, ENT_QUOTES, 'UTF-8') ?>="<?= htmlspecialchars((string) $attrValue, ENT_QUOTES, 'UTF-8') ?>"
                        <?php endforeach; ?>
                        class="<?= htmlspecialchars(trim('btn btn-outline-secondary btn-sm list-toolbar-side-btn ' . $class), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <?php if ($icon !== ''): ?><i class="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?> me-1" aria-hidden="true"></i><?php endif; ?>
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php else: ?>
                    <button
                        <?php foreach ($attrs as $attrName => $attrValue): ?>
                            <?= htmlspecialchars((string) $attrName, ENT_QUOTES, 'UTF-8') ?>="<?= htmlspecialchars((string) $attrValue, ENT_QUOTES, 'UTF-8') ?>"
                        <?php endforeach; ?>
                        class="<?= htmlspecialchars(trim('btn btn-outline-secondary btn-sm list-toolbar-side-btn ' . $class), ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <?php if ($icon !== ''): ?><i class="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?> me-1" aria-hidden="true"></i><?php endif; ?>
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </form>
    <?php
};
?>
<style>
    .list-toolbar-search-form {
        margin-bottom: 1rem;
    }

    .list-toolbar-search-row {
        row-gap: .5rem;
    }

    .list-toolbar-search-group {
        overflow: hidden;
        border: 1px solid rgba(43, 94, 94, 0.18);
        border-radius: .7rem;
        background: rgba(255, 255, 255, 0.88);
        box-shadow: 0 6px 18px rgba(18, 38, 63, 0.05);
    }

    .list-toolbar-search-group:focus-within {
        border-color: rgba(43, 94, 94, 0.32);
        box-shadow: 0 0 0 .2rem rgba(43, 94, 94, 0.08);
    }

    .list-toolbar-search-input {
        min-height: 38px;
        border: 0;
        background: transparent;
        box-shadow: none !important;
        padding-inline: .85rem;
    }

    .list-toolbar-search-input::placeholder {
        color: #7c8b95;
    }

    .list-toolbar-search-btn,
    .list-toolbar-clear-btn,
    .list-toolbar-side-btn {
        min-height: 38px;
        padding-inline: .9rem;
        border-width: 0;
        font-weight: 600;
    }

    .list-toolbar-search-btn {
        border-left: 1px solid rgba(43, 94, 94, 0.12);
        border-radius: 0 !important;
    }

    .list-toolbar-clear-btn {
        border-radius: .7rem;
        border: 1px solid rgba(43, 94, 94, 0.18);
        background: rgba(255, 255, 255, 0.72);
    }

    .list-toolbar-side-btn {
        border-radius: .7rem;
        border: 1px solid rgba(43, 94, 94, 0.18);
        background: rgba(255, 255, 255, 0.72);
    }

    .list-toolbar-clear-btn:hover,
    .list-toolbar-clear-btn:focus,
    .list-toolbar-side-btn:hover,
    .list-toolbar-side-btn:focus {
        border-color: rgba(43, 94, 94, 0.3);
        background: rgba(255, 255, 255, 0.92);
    }

    .list-toolbar-simple-btn:hover,
    .list-toolbar-simple-btn:focus,
    .list-toolbar-simple-btn:active,
    .list-toolbar-compact-btn:hover,
    .list-toolbar-compact-btn:focus,
    .list-toolbar-compact-btn:active {
        color: #fff !important;
    }

    .list-toolbar-simple-btn:hover i,
    .list-toolbar-simple-btn:focus i,
    .list-toolbar-simple-btn:active i,
    .list-toolbar-compact-btn:hover i,
    .list-toolbar-compact-btn:focus i,
    .list-toolbar-compact-btn:active i {
        color: #fff !important;
    }

    .list-toolbar-active-chip {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .45rem .8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid rgba(43, 94, 94, 0.12);
        color: #334e56;
        font-size: .8rem;
        font-weight: 500;
    }

    .list-toolbar-active-chip-label {
        font-weight: 700;
        color: #274c4c;
    }

    .list-toolbar-active-chip-clear {
        color: #7c8b95;
        text-decoration: none;
        font-weight: 700;
        line-height: 1;
    }

    .list-toolbar-active-chip-clear:hover,
    .list-toolbar-active-chip-clear:focus {
        color: #1f6f68;
    }

    .list-toolbar-active-clear-all {
        color: #5f6f78;
        font-weight: 600;
    }

    .list-toolbar-active-clear-all:hover,
    .list-toolbar-active-clear-all:focus {
        color: #1f6f68;
    }

    .list-toolbar-compact-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: end;
        gap: .75rem;
        justify-content: center;
        margin-bottom: 1rem;
        padding: .35rem 0 0;
    }

    .list-toolbar-compact-field {
        flex: 0 0 auto;
    }

    .list-toolbar-compact-control {
        display: flex;
        align-items: center;
        gap: .5rem;
        min-height: 38px;
        width: max-content;
        max-width: 100%;
    }

    .list-toolbar-compact-label {
        margin: 0;
        font-size: .74rem;
        font-weight: 700;
        color: #6c757d;
        letter-spacing: .02em;
        white-space: nowrap;
    }

    .list-toolbar-compact-select {
        width: 160px;
        min-width: 160px;
        min-height: 38px;
        border-radius: .7rem;
        border-color: rgba(43, 94, 94, 0.16);
        background-color: rgba(255, 255, 255, 0.88);
        box-shadow: none;
        font-weight: 500;
        color: #334e56;
        padding-top: .35rem;
        padding-bottom: .35rem;
    }

    .list-toolbar-compact-select:focus {
        border-color: rgba(43, 94, 94, 0.28);
        box-shadow: 0 0 0 .2rem rgba(43, 94, 94, 0.08);
    }

    .list-toolbar-compact-actions {
        display: flex;
        align-items: end;
        gap: .5rem;
        padding-bottom: 1px;
        flex: 0 0 auto;
    }

    .list-toolbar-compact-toggle {
        min-height: 38px;
        padding-inline: .9rem;
        border-radius: .65rem;
        border-color: rgba(43, 94, 94, 0.18);
        background: rgba(255, 255, 255, 0.72);
        font-weight: 600;
    }

    .list-toolbar-compact-toggle:hover,
    .list-toolbar-compact-toggle:focus {
        border-color: rgba(43, 94, 94, 0.3);
        background: rgba(255, 255, 255, 0.92);
    }

    .list-toolbar-compact-apply {
        min-height: 40px;
        padding-inline: 1rem;
        border-radius: .7rem;
        font-weight: 600;
    }

    .list-toolbar-compact-clear {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        color: #5f6f78 !important;
        font-weight: 600;
    }

    .list-toolbar-compact-clear:hover,
    .list-toolbar-compact-clear:focus {
        color: #1f6f68 !important;
    }

    @media (max-width: 991.98px) {
        .list-toolbar-compact-filters {
            justify-content: flex-start;
        }

        .list-toolbar-compact-field {
            flex: 1 1 100%;
        }

        .list-toolbar-compact-actions {
            width: 100%;
            justify-content: flex-end;
        }

        .list-toolbar-compact-control {
            width: 100%;
        }

        .list-toolbar-compact-select {
            width: 100%;
            min-width: 0;
            flex: 1 1 auto;
        }
    }

    @media (max-width: 767.98px) {
        .list-toolbar-search-group {
            width: 100%;
        }

        .list-toolbar-clear-btn,
        .list-toolbar-side-btn {
            min-height: 38px;
        }
    }
</style>
<?php

if ($mode !== 'compact') {
    $searchConfig = is_array($listToolbarConfig['searchConfig'] ?? null) ? $listToolbarConfig['searchConfig'] : [];
    $filterBarGroups = is_array($listToolbarConfig['filterBarGroups'] ?? null) ? $listToolbarConfig['filterBarGroups'] : [];
    $searchAction = (string) ($searchConfig['action'] ?? '');
    $searchMethod = strtoupper((string) ($searchConfig['method'] ?? 'GET'));
    $searchFields = is_array($searchConfig['fields'] ?? null) ? $searchConfig['fields'] : [];
    $searchField = is_array($searchFields[0] ?? null) ? $searchFields[0] : [];
    $searchName = (string) ($searchField['name'] ?? 'q');
    $searchValue = (string) ($searchField['value'] ?? '');
    $searchPlaceholder = (string) ($searchField['placeholder'] ?? '');
    $submitLabel = (string) ($searchConfig['submitLabel'] ?? 'Buscar');
    $submitIcon = (string) ($searchConfig['submitIcon'] ?? 'fa fa-search');
    $clearUrl = (string) ($listToolbarConfig['clearUrl'] ?? ($searchConfig['clearUrl'] ?? ''));
    $searchGroupStyle = (string) ($searchConfig['groupStyle'] ?? 'max-width: 520px; width: 100%;');

    $renderSearchToolbar(
        $searchAction,
        $searchMethod,
        $searchName,
        $searchValue,
        $searchPlaceholder,
        $submitLabel,
        $submitIcon,
        $searchGroupStyle,
        'list-toolbar-simple-btn',
        $clearUrl
    );

    $filterBarView = __DIR__ . '/filter-bar.php';
    if (is_file($filterBarView) && $filterBarGroups !== []) {
        include $filterBarView;
    }

    return;
}

$action = (string) ($listToolbarConfig['action'] ?? '');
$method = strtoupper((string) ($listToolbarConfig['method'] ?? 'GET'));
$basePath = (string) ($listToolbarConfig['basePath'] ?? $action);
$searchConfig = is_array($listToolbarConfig['searchConfig'] ?? null) ? $listToolbarConfig['searchConfig'] : [];
$searchFields = is_array($searchConfig['fields'] ?? null) ? $searchConfig['fields'] : [];
$searchField = is_array($searchFields[0] ?? null) ? $searchFields[0] : [];
$searchName = (string) ($searchField['name'] ?? 'q');
$searchValue = (string) ($searchField['value'] ?? '');
$searchPlaceholder = (string) ($searchField['placeholder'] ?? '');
$submitLabel = (string) ($searchConfig['submitLabel'] ?? 'Buscar');
$submitIcon = (string) ($searchConfig['submitIcon'] ?? 'fa fa-search');
$searchGroupStyle = (string) ($searchConfig['groupStyle'] ?? 'max-width: 560px; width: 100%;');
$clearUrl = (string) ($listToolbarConfig['clearUrl'] ?? ($searchConfig['clearUrl'] ?? ''));
$filters = is_array($listToolbarConfig['filters'] ?? null) ? $listToolbarConfig['filters'] : [];
$queryParams = is_array($listToolbarConfig['queryParams'] ?? null) ? $listToolbarConfig['queryParams'] : [];
$collapseId = (string) ($listToolbarConfig['collapseId'] ?? 'listToolbarFilters');
$toggleLabel = (string) ($listToolbarConfig['toggleLabel'] ?? 'Filtros');
$applyLabel = (string) ($listToolbarConfig['applyLabel'] ?? 'Aplicar');
$clearLabel = (string) ($listToolbarConfig['clearLabel'] ?? 'Limpiar');
$showActiveFilters = ($listToolbarConfig['showActiveFilters'] ?? true) !== false;

$activeFilterCount = 0;
$activeFilters = [];

foreach ($filters as $filter) {
    $filter = is_array($filter) ? $filter : [];
    $name = (string) ($filter['name'] ?? '');
    if ($name === '') {
        continue;
    }

    $currentValue = trim((string) ($filter['value'] ?? ($queryParams[$name] ?? '')));
    if ($currentValue === '') {
        continue;
    }

    $activeFilterCount++;
    $label = (string) ($filter['label'] ?? $name);
    $optionLabel = $currentValue;

    foreach ((array) ($filter['options'] ?? []) as $option) {
        $option = is_array($option) ? $option : [];
        if ((string) ($option['value'] ?? '') === $currentValue) {
            $optionLabel = (string) ($option['label'] ?? $currentValue);
            break;
        }
    }

    $clearParams = $queryParams;
    unset($clearParams[$name], $clearParams['page']);
    $clearParams = array_filter($clearParams, static fn($value) => $value !== '' && $value !== null);
    $clearQs = $clearParams === [] ? '' : '?' . http_build_query($clearParams);

    $activeFilters[] = [
        'label' => $label,
        'value' => $optionLabel,
        'clearUrl' => $basePath . $clearQs,
    ];
}

$filtersOpen = $activeFilterCount > 0 || (($listToolbarConfig['open'] ?? false) === true);
$hiddenFields = [];
foreach ($filters as $filter) {
    $filter = is_array($filter) ? $filter : [];
    $filterName = (string) ($filter['name'] ?? '');
    $filterValue = trim((string) ($filter['value'] ?? ($queryParams[$filterName] ?? '')));
    if ($filterName !== '' && $filterValue !== '') {
        $hiddenFields[] = ['name' => $filterName, 'value' => $filterValue];
    }
}

$sideButtons = [];
if ($filters !== []) {
    $sideButtons[] = [
        'tag' => 'button',
        'label' => $toggleLabel . ($activeFilterCount > 0 ? ' (' . $activeFilterCount . ')' : ''),
        'icon' => 'fa fa-sliders',
        'class' => 'list-toolbar-compact-btn list-toolbar-compact-toggle',
        'attrs' => [
            'type' => 'button',
            'data-bs-toggle' => 'collapse',
            'data-bs-target' => '#' . $collapseId,
            'aria-expanded' => $filtersOpen ? 'true' : 'false',
            'aria-controls' => $collapseId,
        ],
    ];
}

$renderSearchToolbar(
    $action,
    $method,
    $searchName,
    $searchValue,
    $searchPlaceholder,
    $submitLabel,
    $submitIcon,
    $searchGroupStyle,
    'list-toolbar-compact-btn',
    '',
    'Limpiar',
    $hiddenFields,
    $sideButtons
);
?>

<?php if ($showActiveFilters && $activeFilters !== []): ?>
    <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 mb-3">
        <?php foreach ($activeFilters as $activeFilter): ?>
            <span class="list-toolbar-active-chip">
                <span class="list-toolbar-active-chip-label"><?= htmlspecialchars($activeFilter['label'], ENT_QUOTES, 'UTF-8') ?>:</span>
                <?= htmlspecialchars($activeFilter['value'], ENT_QUOTES, 'UTF-8') ?>
                <a
                    href="<?= htmlspecialchars(\Core\Url::to($activeFilter['clearUrl']), ENT_QUOTES, 'UTF-8') ?>"
                    class="list-toolbar-active-chip-clear ms-1"
                    aria-label="Quitar filtro <?= htmlspecialchars($activeFilter['label'], ENT_QUOTES, 'UTF-8') ?>"
                >×</a>
            </span>
        <?php endforeach; ?>

        <?php if ($clearUrl !== ''): ?>
            <a href="<?= htmlspecialchars(\Core\Url::to($clearUrl), ENT_QUOTES, 'UTF-8') ?>" class="small text-decoration-none list-toolbar-active-clear-all">
                Limpiar filtros
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($filters !== []): ?>
    <div class="collapse<?= $filtersOpen ? ' show' : '' ?>" id="<?= htmlspecialchars($collapseId, ENT_QUOTES, 'UTF-8') ?>">
        <form method="<?= htmlspecialchars($method, ENT_QUOTES, 'UTF-8') ?>" action="<?= htmlspecialchars(\Core\Url::to($action), ENT_QUOTES, 'UTF-8') ?>" class="list-toolbar-compact-filters">
            <input type="hidden" name="<?= htmlspecialchars($searchName, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8') ?>">

            <?php foreach ($filters as $filter): ?>
                <?php
                $filter = is_array($filter) ? $filter : [];
                $name = (string) ($filter['name'] ?? '');
                if ($name === '') {
                    continue;
                }
                $label = (string) ($filter['label'] ?? $name);
                $allLabel = (string) ($filter['allLabel'] ?? 'Todos');
                $value = trim((string) ($filter['value'] ?? ($queryParams[$name] ?? '')));
                $options = is_array($filter['options'] ?? null) ? $filter['options'] : [];
                ?>
                <div class="list-toolbar-compact-field">
                    <div class="list-toolbar-compact-control">
                        <label for="<?= htmlspecialchars($collapseId . '-' . $name, ENT_QUOTES, 'UTF-8') ?>" class="list-toolbar-compact-label">
                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                        </label>
                        <select
                            id="<?= htmlspecialchars($collapseId . '-' . $name, ENT_QUOTES, 'UTF-8') ?>"
                            name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                            class="form-select form-select-sm list-toolbar-compact-select"
                        >
                            <option value=""><?= htmlspecialchars($allLabel, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php foreach ($options as $option): ?>
                                <?php
                                $option = is_array($option) ? $option : [];
                                $optionValue = (string) ($option['value'] ?? '');
                                $optionLabel = (string) ($option['label'] ?? $optionValue);
                                ?>
                                <option value="<?= htmlspecialchars($optionValue, ENT_QUOTES, 'UTF-8') ?>"<?= $optionValue === $value ? ' selected' : '' ?>>
                                    <?= htmlspecialchars($optionLabel, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="list-toolbar-compact-actions">
                <button type="submit" class="btn btn-primary btn-sm list-toolbar-compact-apply">
                    <?= htmlspecialchars($applyLabel, ENT_QUOTES, 'UTF-8') ?>
                </button>
                <?php if ($clearUrl !== ''): ?>
                    <a href="<?= htmlspecialchars(\Core\Url::to($clearUrl), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-link btn-sm text-decoration-none px-2 list-toolbar-compact-clear">
                        <?= htmlspecialchars($clearLabel, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
<?php endif; ?>
