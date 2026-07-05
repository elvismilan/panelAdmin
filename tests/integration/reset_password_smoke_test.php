<?php

declare(strict_types=1);

use Tests\Support\ResetPasswordSmoke;

test('Reset password smoke permanece cubierto', function (): void {
    if (trim((string) ($_ENV['TEST_RESET_EMAIL'] ?? '')) === '') {
        skip_test('TEST_RESET_EMAIL no definido.');
    }

    ResetPasswordSmoke::run();
    assert_true(true);
});
