<?php
/**
 * Email view: aviso al titular del sitio de un nuevo mensaje del formulario
 * de Contacto (Nombre, Telefono opcional, Correo, Mensaje).
 * Variables esperadas: $siteTitle, $nombre, $telefono, $email, $mensaje, $address, $country
 */
$siteTitle = (string) ($siteTitle ?? 'Mi Sitio');
$nombre    = htmlspecialchars((string) ($nombre  ?? ''), ENT_QUOTES, 'UTF-8');
$telefono  = htmlspecialchars((string) ($telefono ?? ''), ENT_QUOTES, 'UTF-8');
$email     = htmlspecialchars((string) ($email   ?? ''), ENT_QUOTES, 'UTF-8');
$mensaje   = nl2br(htmlspecialchars((string) ($mensaje ?? ''), ENT_QUOTES, 'UTF-8'));
$address   = (string) ($address ?? '');
$country   = (string) ($country ?? '');
$siteRoot  = rtrim(\PanelAdmin\Core\Url::to('/'), '/');
$logoRaw   = ltrim(preg_replace('#^public/#', '', (string) ($_ENV['LOGO'] ?? 'assets/theme-one/images/logo/logo-azul.svg')), '/');
$logoUrl   = $siteRoot . '/' . $logoRaw;
$primary   = '#113655';
$eyebrow   = 'Nuevo mensaje de contacto';

include __DIR__ . '/shared/header.php';
?>
                  <h6 style="color:<?= $primary ?>">Tienes un nuevo mensaje desde el formulario de Contacto</h6>
                  <p>Un visitante completó el formulario de contacto del sitio. Estos son los datos:</p>

                  <table style="width:100%; border-collapse:collapse; margin:18px 0">
                    <tbody>
                      <tr>
                        <td style="padding:8px 10px; border:1px solid #e7e9ec; background:#f6f7fb; width:150px; font-size:13px; font-weight:600; color:<?= $primary ?>">Nombre</td>
                        <td style="padding:8px 10px; border:1px solid #e7e9ec; font-size:13px"><?= $nombre ?></td>
                      </tr>
                      <?php if ($telefono !== ''): ?>
                      <tr>
                        <td style="padding:8px 10px; border:1px solid #e7e9ec; background:#f6f7fb; font-size:13px; font-weight:600; color:<?= $primary ?>">Teléfono</td>
                        <td style="padding:8px 10px; border:1px solid #e7e9ec; font-size:13px"><a href="tel:<?= $telefono ?>" style="color:<?= $primary ?>"><?= $telefono ?></a></td>
                      </tr>
                      <?php endif; ?>
                      <?php if ($email !== ''): ?>
                      <tr>
                        <td style="padding:8px 10px; border:1px solid #e7e9ec; background:#f6f7fb; font-size:13px; font-weight:600; color:<?= $primary ?>">Email</td>
                        <td style="padding:8px 10px; border:1px solid #e7e9ec; font-size:13px"><a href="mailto:<?= $email ?>" style="color:<?= $primary ?>"><?= $email ?></a></td>
                      </tr>
                      <?php endif; ?>
                      <tr>
                        <td style="padding:8px 10px; border:1px solid #e7e9ec; background:#f6f7fb; font-size:13px; font-weight:600; color:<?= $primary ?>; vertical-align:top">Mensaje</td>
                        <td style="padding:8px 10px; border:1px solid #e7e9ec; font-size:13px"><?= $mensaje ?></td>
                      </tr>
                    </tbody>
                  </table>

                  <?php if ($email !== ''): ?>
                  <p class="text-center" style="margin:26px 0 8px 0">
                    <a href="mailto:<?= $email ?>"
                       style="display:inline-block; background-color:<?= $primary ?>; color:#ffffff; padding:12px 28px; border-radius:6px; font-size:13px; font-weight:600">
                      Responder por correo
                    </a>
                  </p>
                  <?php elseif ($telefono !== ''): ?>
                  <p class="text-center" style="margin:26px 0 8px 0">
                    <a href="tel:<?= $telefono ?>"
                       style="display:inline-block; background-color:<?= $primary ?>; color:#ffffff; padding:12px 28px; border-radius:6px; font-size:13px; font-weight:600">
                      Llamar
                    </a>
                  </p>
                  <?php endif; ?>
<?php include __DIR__ . '/shared/footer.php'; ?>
