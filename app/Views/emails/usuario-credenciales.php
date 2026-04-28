<?php
/**
 * Email view: acceso para nuevo usuario
 * Variables esperadas: $userName, $usuId, $loginUrl, $resetUrl, $expiryMinutes, $siteTitle, $address, $country
 */
$siteTitle = htmlspecialchars((string) ($siteTitle ?? 'Web Revolution'), ENT_QUOTES, 'UTF-8');
$userName  = htmlspecialchars((string) ($userName  ?? 'Usuario'),        ENT_QUOTES, 'UTF-8');
$siteRoot  = rtrim((string) ($_ENV['SITE_ROOT'] ?? ''), '/');
$logoRaw   = ltrim(preg_replace('#^public/#', '', (string) ($_ENV['LOGO'] ?? 'assets/theme-one/images/logo/logo-h.png')), '/');
$logoUrl   = htmlspecialchars($siteRoot . '/' . $logoRaw, ENT_QUOTES, 'UTF-8');
$usuId     = htmlspecialchars((string) ($usuId     ?? ''),               ENT_QUOTES, 'UTF-8');
$loginUrl  = htmlspecialchars((string) ($loginUrl  ?? '#'),              ENT_QUOTES, 'UTF-8');
$resetUrl  = htmlspecialchars((string) ($resetUrl  ?? ''),               ENT_QUOTES, 'UTF-8');
$expiryMinutes = (int) ($expiryMinutes ?? 60);
$address   = (string) ($address ?? '');
$country   = htmlspecialchars((string) ($country ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bienvenido — <?= $siteTitle ?></title>
  <link href="https://fonts.googleapis.com/css?family=Work+Sans:400,600,700" rel="stylesheet">
  <style>
    body { width: 650px; font-family: 'Work Sans', sans-serif; background-color: #f6f7fb; display: block; margin: 30px auto; }
    a    { text-decoration: none; }
    p    { font-size: 13px; line-height: 1.7; letter-spacing: 0.7px; margin-top: 0; }
    h6   { font-size: 16px; margin: 0 0 18px 0; }
    .credential-box {
      background-color: #f0f4f3;
      border-left: 4px solid #24695c;
      border-radius: 4px;
      padding: 14px 18px;
      margin: 18px 0;
    }
    .credential-label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 2px 0; }
    .credential-value { font-size: 15px; font-weight: 600; color: #222; margin: 0 0 10px 0; font-family: monospace; }
    .credential-value:last-child { margin-bottom: 0; }
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
                        <td><img src="<?= $logoUrl ?>" alt="<?= $siteTitle ?>" style="height:40px; width:auto; display:block;"></td>
                        <td style="text-align:right; color:#999"><span style="font-size:14px">Acceso al Sistema</span></td>
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
                  <h6 style="font-weight:600">Bienvenido al sistema</h6>
                  <p>Hola <strong><?= $userName ?></strong>,</p>
                  <p>Se ha creado una cuenta de acceso para ti en <strong><?= $siteTitle ?></strong>.</p>

                  <div class="credential-box">
                    <p class="credential-label">Usuario</p>
                    <p class="credential-value"><?= $usuId ?></p>
                  </div>

                  <?php if ($resetUrl !== ''): ?>
                    <p>Para definir tu contraseña de forma segura, usa este enlace (válido por <?= htmlspecialchars((string) $expiryMinutes, ENT_QUOTES, 'UTF-8') ?> minutos):</p>
                    <p style="text-align:center; margin-top:24px;">
                      <a href="<?= $resetUrl ?>"
                         style="padding:12px 28px; background-color:#24695c; color:#fff; display:inline-block; border-radius:4px; font-weight:600; font-size:14px;">
                        Definir contraseña
                      </a>
                    </p>
                  <?php else: ?>
                    <p>Por seguridad no enviamos contraseñas por correo. Usa la opción <strong>"¿Olvidaste tu contraseña?"</strong> para generar una nueva clave.</p>
                  <?php endif; ?>

                  <p style="font-size:12px; color:#999; text-align:center; margin-top:16px;">
                    Una vez definida tu contraseña, podrás iniciar sesión normalmente.
                  </p>

                  <p style="text-align:center; margin-top:8px;">
                    <a href="<?= $loginUrl ?>" style="font-size:12px; color:#24695c;">Ir al inicio de sesión</a>
                  </p>

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
