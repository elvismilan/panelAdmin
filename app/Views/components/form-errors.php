<?php
$errorMessage = is_string($error ?? null) ? trim($error) : '';
$errorList = [];

if (is_array($errors ?? null)) {
    foreach ($errors as $fieldErrors) {
        foreach ((array) $fieldErrors as $message) {
            $message = trim((string) $message);
            if ($message !== '') {
                $errorList[] = $message;
            }
        }
    }
}

if ($errorMessage !== '') {
    $errorList[] = $errorMessage;
}

$errorList = array_values(array_unique($errorList));
?>
<?php if (!empty($errorList)): ?>
    <div class="row justify-content-center">
        <div class="col-sm-7 mb-3">
            <div class="alert alert-danger inverse alert-dismissible fade show" role="alert" data-auto-dismiss="10000">
                <i class="icon-thumb-down"></i>
                <?php if (count($errorList) === 1): ?>
                    <p><?= $errorList[0] ?></p>
                <?php else: ?>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errorList as $message): ?>
                            <li><?= $message ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
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
<?php endif; ?>
