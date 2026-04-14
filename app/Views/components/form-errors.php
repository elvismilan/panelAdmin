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
    <?php foreach ($errorList as $message): ?>
        <div class="alert alert-danger inverse alert-dismissible fade show" role="alert">
            <i class="icon-thumb-down"></i>
            <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
