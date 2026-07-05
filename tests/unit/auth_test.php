<?php

declare(strict_types=1);

use Core\Auth;

test('Auth login check user y logout funcionan con session', function (): void {
    assert_false(Auth::check());
    assert_null(Auth::user());

    $user = ['id' => 'admin', 'group' => 'ADMIN'];
    Auth::login($user);

    assert_true(Auth::check());
    assert_same($user, Auth::user());

    Auth::logout();

    assert_false(Auth::check());
    assert_null(Auth::user());
});
