<?php
/**
 * Email view: recuperacion de contrasena
 * Variables esperadas: $resetUrl, $userName, $siteTitle, $address, $country, $expiryMinutes
 */
$siteTitle     = htmlspecialchars((string) ($siteTitle     ?? 'Web Revolution'),  ENT_QUOTES, 'UTF-8');
$userName      = htmlspecialchars((string) ($userName      ?? 'Usuario'),          ENT_QUOTES, 'UTF-8');
$resetUrl      = htmlspecialchars((string) ($resetUrl      ?? '#'),                ENT_QUOTES, 'UTF-8');
$address       = (string) ($address  ?? '');
$country       = htmlspecialchars((string) ($country       ?? ''),                 ENT_QUOTES, 'UTF-8');
$expiryMinutes = (int)    ($expiryMinutes ?? 60);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar Contraseña — <?= $siteTitle ?></title>
  <link href="https://fonts.googleapis.com/css?family=Work+Sans:400,600,700" rel="stylesheet">
  <style>
    body { width: 650px; font-family: 'Work Sans', sans-serif; background-color: #f6f7fb; display: block; margin: 30px auto; }
    a    { text-decoration: none; }
    p    { font-size: 13px; line-height: 1.7; letter-spacing: 0.7px; margin-top: 0; }
    h6   { font-size: 16px; margin: 0 0 18px 0; }
    .text-center { text-align: center; }
  </style>
</head>
<body>
  <table style="width:100%">
    <tbody>
      <tr>
        <td>

          <!-- Header -->
          <table style="background-color:#f6f7fb; width:100%">
            <tbody>
              <tr>
                <td>
                  <table style="width:650px; margin:0 auto 30px auto">
                    <tbody>
                      <tr>
                        <td style="font-size:20px; font-weight:700; color:#24695c;"><?= $siteTitle ?></td>
                        <td style="text-align:right; color:#999"><span style="font-size:14px">Recuperación de Contraseña</span></td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Body -->
          <table style="width:650px; margin:0 auto; background-color:#fff; border-radius:8px">
            <tbody>
              <tr>
                <td style="padding:30px">
                  <h6 style="font-weight:600">Restablecer tu Contraseña</h6>
                  <p>Hola <strong><?= $userName ?></strong>,</p>
                  <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta. Si fuiste tú, haz clic en el botón de abajo para continuar.</p>
                  <p style="text-align:center">
                    <a href="<?= $resetUrl ?>"
                       style="padding:12px 28px; background-color:#24695c; color:#fff; display:inline-block; border-radius:4px; font-weight:600; font-size:14px;">
                      Restablecer Contraseña
                    </a>
                  </p>
                  <p style="font-size:12px; color:#999; text-align:center">
                    Este enlace expira en <strong><?= $expiryMinutes ?> minutos</strong>.
                  </p>
                  <p>Si no solicitaste este cambio, puedes ignorar este correo. Tu contraseña no será modificada.</p>
                  <p style="margin-bottom:0">
                    Saludos,<br>
                    <strong><?= $siteTitle ?></strong>
                  </p>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Footer -->
          <table style="width:650px; margin:30px auto 0 auto">
            <tbody>
              <tr style="text-align:center">
                <td>
                  <?php if ($address !== ''): ?>
                    <p style="color:#999; margin-bottom:0"><?= $address ?></p>
                  <?php endif; ?>
                  <?php if ($country !== ''): ?>
                    <p style="color:#999; margin-bottom:0"><?= $country ?></p>
                  <?php endif; ?>
                  <p style="color:#999; margin-bottom:0">Powered by <?= $siteTitle ?></p>
                </td>
              </tr>
            </tbody>
          </table>

        </td>
      </tr>
    </tbody>
  </table>
</body>
</html>
