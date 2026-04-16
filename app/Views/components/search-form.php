<?php
$searchConfig = is_array($searchConfig ?? null) ? $searchConfig : [];
$searchAction = (string) ($searchConfig['action'] ?? '');
$searchMethod = strtoupper((string) ($searchConfig['method'] ?? 'GET'));
$searchFields = is_array($searchConfig['fields'] ?? null) ? $searchConfig['fields'] : [];
$submitLabel = (string) ($searchConfig['submitLabel'] ?? 'Buscar');
$submitIcon = (string) ($searchConfig['submitIcon'] ?? 'fa fa-search');
$clearUrl = (string) ($searchConfig['clearUrl'] ?? '');
$formClass = (string) ($searchConfig['formClass'] ?? 'mb-3 d-flex justify-content-center');
$groupClass = (string) ($searchConfig['groupClass'] ?? 'input-group input-group-sm');
$groupStyle = (string) ($searchConfig['groupStyle'] ?? 'max-width: 520px; width: 100%;');
?>
<?php if ($searchAction !== '' && !empty($searchFields)): ?>
    <form method="<?= htmlspecialchars($searchMethod, ENT_QUOTES, 'UTF-8') ?>" action="<?= htmlspecialchars($searchAction, ENT_QUOTES, 'UTF-8') ?>" class="<?= htmlspecialchars($formClass, ENT_QUOTES, 'UTF-8') ?>">
        <div class="<?= htmlspecialchars($groupClass, ENT_QUOTES, 'UTF-8') ?>" style="<?= htmlspecialchars($groupStyle, ENT_QUOTES, 'UTF-8') ?>">
            <?php foreach ($searchFields as $index => $field): ?>
                <?php
                $field = is_array($field) ? $field : [];
                $name = (string) ($field['name'] ?? '');
                if ($name === '') {
                    continue;
                }
                $type = strtolower((string) ($field['type'] ?? 'text'));
                $placeholder = (string) ($field['placeholder'] ?? '');
                $value = (string) ($field['value'] ?? '');
                $options = is_array($field['options'] ?? null) ? $field['options'] : [];
                $icon = (string) ($field['icon'] ?? ($index === 0 ? 'fa fa-search' : ''));
                ?>

                <?php if ($icon !== ''): ?>
                    <span class="input-group-text"><i class="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i></span>
                <?php endif; ?>

                <?php if ($type === 'select'): ?>
                    <select name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" class="form-select">
                        <?php foreach ($options as $opt): ?>
                            <?php
                            $opt = is_array($opt) ? $opt : [];
                            $optValue = (string) ($opt['value'] ?? '');
                            $optLabel = (string) ($opt['label'] ?? $optValue);
                            ?>
                            <option value="<?= htmlspecialchars($optValue, ENT_QUOTES, 'UTF-8') ?>"<?= $optValue === $value ? ' selected' : '' ?>>
                                <?= htmlspecialchars($optLabel, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input
                        type="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>"
                        name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                        class="form-control"
                        placeholder="<?= htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') ?>"
                        value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                    >
                <?php endif; ?>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-outline-primary">
                <i class="<?= htmlspecialchars($submitIcon, ENT_QUOTES, 'UTF-8') ?> me-1" aria-hidden="true"></i>
                <?= htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8') ?>
            </button>

            <?php if ($clearUrl !== ''): ?>
                <a href="<?= htmlspecialchars($clearUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary">Limpiar</a>
            <?php endif; ?>
        </div>
    </form>
<?php endif; ?>
