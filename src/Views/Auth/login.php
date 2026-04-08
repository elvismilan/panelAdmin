<section>         
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-12">
                <div class="login-card">
                    <form class="theme-form login-form" action="<?= SITE_ROOT ?>login" method="POST">
                        <h4 class="text-center mb-2"><?= html_entity_decode('Iniciar Sesión')?></h4>
                        <h6 class="text-center">¡Bienvenido de nuevo! Ingrese a su cuenta.</h6>
                        <div class="form-group">
                            <label><?= html_entity_decode('Correo Electronico')?></label>
                            <div class="input-group"><span class="input-group-text"><i class="icon-email"></i></span>
                                <input class="form-control" type="email" placeholder="Test@gmail.com">
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?= html_entity_decode('Contraseña')?></label>
                            <div class="input-group"><span class="input-group-text"><i class="icon-lock"></i></span>
                                <input class="form-control" type="password" name="login[password]" placeholder="*********">
                                <div class="show-hide">
                                    <span class="show"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="checkbox">
                                <input id="checkbox1" type="checkbox">
                                <label for="checkbox1">Recordarme</label>
                            </div>
                            <a class="link" href="forget-password.html">Olvide mi contraseña?</a>
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