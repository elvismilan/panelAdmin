<?php

declare(strict_types=1);

use Tests\Support\ResetPasswordSmoke;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

function fail(string $message): void
{
    fwrite(STDERR, "[FAIL] {$message}\n");
    exit(1);
}

function ok(string $message): void
{
    fwrite(STDOUT, "[OK] {$message}\n");
}

try {
    ResetPasswordSmoke::run();
    ok('Smoke test de reset de contraseña completado');
} catch (Throwable $e) {
    fail($e->getMessage());
}
