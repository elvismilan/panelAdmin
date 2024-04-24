    <!-- page-wrapper end-->
    <!-- latest jquery-->
    <script src="<?= SITE_ROOT ?>assets/js/jquery-3.5.1.min.js"></script>
    <!-- feather icon js-->
    <script src="<?= SITE_ROOT ?>assets/js/icons/feather-icon/feather.min.js"></script>
    <script src="<?= SITE_ROOT ?>assets/js/icons/feather-icon/feather-icon.js"></script>
    <!-- Sidebar jquery-->
    <script src="<?= SITE_ROOT ?>assets/js/sidebar-menu.js"></script>
    <script src="<?= SITE_ROOT ?>assets/js/config.js"></script>
    <!-- Bootstrap js-->
    <script src="<?= SITE_ROOT ?>assets/js/bootstrap/popper.min.js"></script>
    <script src="<?= SITE_ROOT ?>assets/js/bootstrap/bootstrap.min.js"></script>
    <!-- Plugins JS start-->
    <?php 
        if (isset($this->js)) :
            foreach ($this->js as $js)
            {
                ?>
                    <script type="text/javascript" src="<?= SITE_ROOT . $js ?>"></script>
                <?php
            }
        endif;
    ?>
    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script src="<?= SITE_ROOT ?>assets/js/script.js"></script>
    <!-- login js-->
    <!-- Plugin used-->
  </body>
</html>