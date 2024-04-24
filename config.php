<?php

    $params = require(dirname(__FILE__) . '/settings.php');

    define('SITE_ROOT', $params['url']);
    define('SITE_TITLE', 'Web Revolution');
    define('LOGO','public/assets/media/logos/beto-color.png');
    define('LIBS', 'libs/');
    define('PAHT_FILE', dirname(__FILE__));
    define('DB_TYPE', $params['db_type']);
    define('DB_HOST', $params['db_host']);
    define('DB_NAME', $params['db_name']);
    define('DB_USER', $params['db_user']);
    define('DB_PASS', $params['db_pass']);

    define('_country', 'Santa Cruz - Bolivia');
    define('ADDRESS', 'C/ Manuel Maria Caballero, radial 16<br> Entre 3er anillo interno y externo');

    define('HASH_GENERAL_KEY', 'MixitUp200');
    // This is for database passwords only
    define('HASH_PASSWORD_KEY', 'catsFLYhigh2000miles');