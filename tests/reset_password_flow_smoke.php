<?php

declare(strict_types=1);

use App\Models\PasswordResetModel;
use Core\Database;

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

$email = strtolower(trim((string) ($_ENV['TEST_RESET_EMAIL'] ?? '')));
if ($email === '') {
    fail('Define TEST_RESET_EMAIL en .env para ejecutar este smoke test.');
}

$model = new PasswordResetModel();
$token = $model->createToken($email);
if ($token === '') {
    fail('No se pudo crear token de reset.');
}

ok('Token creado');

$newPassword = 'Tmp#' . bin2hex(random_bytes(4));
$consumedEmail = $model->consumeTokenAndUpdatePassword($token, $newPassword);
if ($consumedEmail === null) {
    fail('El token valido no pudo consumirse en primer uso.');
}

ok('Primer consumo de token exitoso');

$secondTry = $model->consumeTokenAndUpdatePassword($token, $newPassword . 'A');
if ($secondTry !== null) {
    fail('El token fue reutilizado y no deberia ser posible.');
}

ok('Reutilizacion de token bloqueada');

$expiredToken = $model->createToken($email);
$db = Database::fromEnv();
$prefix = trim((string) ($_ENV['DB_PREFIX'] ?? ''));
$candidatePrefixed = $prefix !== '' ? $prefix . 'password_resets' : 'password_resets';
$existsStmt = $db->query(
    'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table',
    ['table' => $candidatePrefixed]
);
$resetsTable = ((int) $existsStmt->fetchColumn() > 0) ? $candidatePrefixed : 'password_resets';
$db->query("UPDATE {$resetsTable} SET created_at = DATE_SUB(NOW(), INTERVAL 2 HOUR) WHERE token = :token", [
    'token' => $expiredToken,
]);

$expiredTry = $model->consumeTokenAndUpdatePassword($expiredToken, $newPassword . 'B');
if ($expiredTry !== null) {
    fail('El token expirado fue aceptado y no deberia.');
}

ok('Token expirado rechazado');
ok('Smoke test de reset de contraseña completado');
