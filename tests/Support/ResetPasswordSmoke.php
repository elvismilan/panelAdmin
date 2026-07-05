<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\PasswordResetModel;
use Core\Database;
use RuntimeException;

final class ResetPasswordSmoke
{
    public static function run(): void
    {
        $email = strtolower(trim((string) ($_ENV['TEST_RESET_EMAIL'] ?? '')));
        if ($email === '') {
            throw new RuntimeException('Define TEST_RESET_EMAIL en .env para ejecutar este smoke test.');
        }

        $model = new PasswordResetModel();
        $token = $model->createToken($email);
        if ($token === '') {
            throw new RuntimeException('No se pudo crear token de reset.');
        }

        $newPassword = 'Tmp#' . bin2hex(random_bytes(4));
        $consumedEmail = $model->consumeTokenAndUpdatePassword($token, $newPassword);
        if ($consumedEmail === null) {
            throw new RuntimeException('El token valido no pudo consumirse en primer uso.');
        }

        $secondTry = $model->consumeTokenAndUpdatePassword($token, $newPassword . 'A');
        if ($secondTry !== null) {
            throw new RuntimeException('El token fue reutilizado y no deberia ser posible.');
        }

        $expiredToken = $model->createToken($email);
        $db = Database::fromEnv();
        $resetsTable = 'wr_password_resets';
        $db->query("UPDATE {$resetsTable} SET created_at = DATE_SUB(NOW(), INTERVAL 2 HOUR) WHERE token = :token", [
            'token' => $expiredToken,
        ]);

        $expiredTry = $model->consumeTokenAndUpdatePassword($expiredToken, $newPassword . 'B');
        if ($expiredTry !== null) {
            throw new RuntimeException('El token expirado fue aceptado y no deberia.');
        }
    }
}
