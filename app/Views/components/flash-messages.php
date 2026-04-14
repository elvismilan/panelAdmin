<?php
$flashMessages = is_array($flashes ?? null) ? $flashes : [];
$typeToIcon = [
    'success' => 'icon-check',
    'danger' => 'icon-thumb-down',
    'warning' => 'icon-alert',
    'info' => 'icon-info',
];
?>
<?php foreach ($flashMessages as $type => $messages): ?>
    <?php foreach ((array) $messages as $message): ?>
        <?php
        $type = trim((string) $type);
        $message = trim((string) $message);
        if ($type === '' || $message === '') {
            continue;
        }
        $icon = $typeToIcon[$type] ?? 'icon-info';
        ?>
        <div class="alert alert-<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?> inverse alert-dismissible fade show" role="alert">
            <i class="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>"></i>
            <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>
