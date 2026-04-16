<?php
$headerFile = dirname(__DIR__) . '/auth-layout-header.php';
$footerFile = dirname(__DIR__) . '/auth-layout-footer.php';
if (is_file($headerFile)) {
    include $headerFile;
}
?>
<!-- page-wrapper Start-->
    <section>
        <div class="container-fluid p-0">
            <div class="row m-0">
                <div class="col-12 p-0">
                    <div class="login-card">
                        <div class="login-main">
                            <form class="theme-form login-form" method="post" action="/forgot-password">
                                <?= $csrfField ?>
                                <h4 class="mb-3">¿Olvidaste tu contraseña?</h4>
                                <p class="mb-3" style="font-size:13px; color:#6c757d;">
                                    Ingresa tu email y te enviaremos un enlace para restablecerla.
                                </p>

                                <?php if (!empty($success)): ?>
                                    <div class="alert alert-success" role="alert">
                                        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (empty($success)): ?>
                                    <div class="form-group">
                                        <label>Correo electrónico</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="icon-email"></i></span>
                                            <input class="form-control"
                                                   type="email"
                                                   name="email"
                                                   required
                                                   placeholder="tu@correo.com"
                                                   autocomplete="email"
                                                   value="<?= htmlspecialchars((string) ($email ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button class="btn btn-primary btn-block" type="submit">
                                            Enviar enlace
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <p class="mt-2 mb-0">
                                    <a class="ms-2" href="/login">Volver al inicio de sesión</a>
                                </p>
                            </form>
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
