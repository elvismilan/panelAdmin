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
        <div class="row justify-content-center">
            <div class="col-sm-8 mb-3">
                <div class="alert alert-<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?> inverse alert-dismissible fade show" role="alert" data-auto-dismiss="10000">
                    <i class="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>"></i>
                    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
                    <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>
<script>
(function () {
    function autoDismiss(el) {
        var delay = parseInt(el.getAttribute('data-auto-dismiss'), 10);
        if (isNaN(delay) || delay <= 0) return;
        el.setAttribute('data-auto-dismiss-init', '1');
        setTimeout(function () {
            el.classList.remove('show');
            setTimeout(function () {
                if (el.parentNode) { el.parentNode.removeChild(el); }
            }, 350);
        }, delay);
    }
    var alerts = document.querySelectorAll('[data-auto-dismiss]:not([data-auto-dismiss-init])');
    for (var i = 0; i < alerts.length; i++) { autoDismiss(alerts[i]); }
})();
</script>
