<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$files = [
    __DIR__ . '/unit/request_test.php',
    __DIR__ . '/unit/router_test.php',
    __DIR__ . '/unit/auth_test.php',
    __DIR__ . '/unit/permission_test.php',
    __DIR__ . '/unit/testimonios_test.php',
];

load_test_files($files);
$failed = run_registered_tests();
exit($failed > 0 ? 1 : 0);
