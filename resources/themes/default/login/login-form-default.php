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
            <div class="row">
                <div class="col-12">
                    <div class="login-card">
                        <form class="theme-form login-form" method="post" action="/login">
                            <?= $csrfField ?>
                            <h4 class="text-center">Iniciar Sesión</h4>
                            <h6 class="text-center">¡Bienvenido de nuevo! Inicia sesión en tu cuenta.</h6>
                            <?php if (!empty($error)): ?>
                                <div class="form-group">
                                    <div class="alert alert-danger" role="alert">
                                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label>Usuario</label>
                                <div class="input-group"><span class="input-group-text"><i class="icon-email"></i></span>
                                    <input class="form-control" name="username" required="" placeholder="usuario" autocomplete="username">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Contraseña</label>
                                <div class="input-group"><span class="input-group-text"><i class="icon-lock"></i></span>
                                    <input class="form-control" type="password" name="password" required="" placeholder="*********" autocomplete="current-password">
                                    <div class="show-hide"><span class="show"></span></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-primary btn-block" type="submit">Ingresar</button>
                            </div>
                        </form>
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
