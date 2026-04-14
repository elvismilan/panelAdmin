    <?php
    $adminAssetBase = isset($adminAssetBase)
      ? (string) $adminAssetBase
      : rtrim((string) ($_ENV['ADMIN_ASSET_BASE'] ?? ($_ENV['LOGIN_OPTION1_ASSET_BASE'] ?? '/assets/theme-one')), '/');
    ?>
        </div>
        <!-- footer start-->
        <footer class="footer">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-6 footer-copyright">
              </div>
              <div class="col-md-6">
                <p class="pull-right mb-0">Copyright 2014-26 © elvismilan Todos los derechos reservados. <i class="fa fa-laptop font-secondary"></i></p>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </div>
    <!-- latest jquery-->
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/jquery-3.5.1.min.js"></script>
    <!-- feather icon js-->
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/icons/feather-icon/feather.min.js"></script>
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/icons/feather-icon/feather-icon.js"></script>
    <!-- Sidebar jquery-->
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/config.js"></script>
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/sidebar-menu.js"></script>
    <!-- Bootstrap js-->
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/bootstrap/popper.min.js"></script>
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/bootstrap/bootstrap.min.js"></script>
    <!-- Plugins JS start-->
    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/script.js"></script>
    <!-- <script src="<?= htmlspecialchars($adminAssetBase, ENT_QUOTES, 'UTF-8') ?>/js/theme-customizer/customizer.js"></script> -->
    <!-- login js-->
    <!-- Plugin used-->
  </body>
</html>