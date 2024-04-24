<!DOCTYPE html>
<html lang="en">
    <head>
        <?php
            include '../src/Views/layouts/components/metas.php';
            include '../src/Views/layouts/components/favicons.php';
        ?>
        <title><?= SITE_TITLE ?></title>
        <?php
            include '../src/Views/layouts/components/fonts.php';
            include '../src/Views/layouts/components/icons.php';
        ?>
        <!-- Plugins css start-->
        <?php
        if (isset($this->css)) :
            foreach ($this->css as $css) :
            ?>
                <link href="<?= SITE_ROOT . $css ?>" rel="stylesheet" type="text/css" />
            <?php
            endforeach;
        endif;
        ?>
        <!-- Plugins css Ends-->
        <?php
            include '../src/Views/layouts/components/styles.php';
        ?>
    </head>
    <body>
        <!-- Loader starts-->
        <div class="loader-wrapper">
            <div class="theme-loader">    
                <div class="loader-p"></div>
            </div>
        </div>
        <!-- Loader ends-->
        <!-- page-wrapper Start-->