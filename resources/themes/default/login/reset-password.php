<?php
$headerFile = dirname(__DIR__) . '/auth-layout-header.php';
$footerFile = dirname(__DIR__) . '/auth-layout-footer.php';
if (is_file($headerFile)) {
    include $headerFile;
}
$token = htmlspecialchars((string) ($token ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!-- page-wrapper Start-->
    <section>
        <div class="container-fluid p-0">
            <div class="row m-0">
                <div class="col-12 p-0">
                    <div class="login-card">
                        <div class="login-main">

                            <?php if (!empty($tokenInvalid)): ?>
                                <!-- Token invalido o expirado -->
                                <div class="text-center">
                                    <h4 class="mb-3">Enlace no válido</h4>
                                    <div class="alert alert-danger" role="alert">
                                        <?= htmlspecialchars($tokenInvalid, ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <a href="<?= htmlspecialchars(\Core\Url::to('/forgot-password'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary btn-block mt-2">
                                        Solicitar nuevo enlace
                                    </a>
                                </div>

                            <?php else: ?>
                                <form class="theme-form login-form" method="post" action="<?= htmlspecialchars(\Core\Url::to('/reset-password/' . $token), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= $csrfField ?>
                                    <h4 class="mb-3">Nueva contraseña</h4>
                                    <p class="mb-3" style="font-size:13px; color:#6c757d;">
                                        Elige una contraseña segura de al menos 8 caracteres.
                                    </p>

                                    <?php if (!empty($error)): ?>
                                        <div class="alert alert-danger" role="alert">
                                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="form-group">
                                        <label>Nueva contraseña</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="icon-lock"></i></span>
                                            <input class="form-control"
                                                   type="password"
                                                   name="password"
                                                   required
                                                   placeholder="*********"
                                                   autocomplete="new-password"
                                                   minlength="8">
                                            <div class="show-hide"><span class="show"></span></div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Confirmar contraseña</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="icon-lock"></i></span>
                                            <input class="form-control"
                                                   type="password"
                                                   name="password_confirm"
                                                   required
                                                   placeholder="*********"
                                                   autocomplete="new-password"
                                                   minlength="8">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button class="btn btn-primary btn-block" type="submit">
                                            Guardar contraseña
                                        </button>
                                    </div>

                                    <p class="mt-2 mb-0">
                                        <a class="ms-2" href="<?= htmlspecialchars(\Core\Url::to('/login'), ENT_QUOTES, 'UTF-8') ?>">Volver al inicio de sesión</a>
                                    </p>
                                </form>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<!-- page-wrapper end-->
<?php
if (is_file($footerFile)) {
    include $footerFile;
}
?>
