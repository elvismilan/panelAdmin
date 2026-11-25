<?php
/**
 * Header compartido de los emails transaccionales (aviso interno al admin).
 * Abre doctype/head/body y las tablas de header + inicio del body.
 * El template que lo incluye debe cerrar con emails/shared/footer.php.
 *
 * Variables esperadas: $siteTitle, $logoUrl, $eyebrow, $primary.
 */
$siteTitle = (string) ($siteTitle ?? 'Mi Sitio');
$logoUrl = (string) ($logoUrl ?? '');
$eyebrow = (string) ($eyebrow ?? '');
$primary = (string) ($primary ?? '#113655');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($eyebrow, ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8') ?></title>
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
                        <td><img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8') ?>" style="height:40px; width:auto; display:block;"></td>
                        <td style="text-align:right; color:#999"><span style="font-size:14px"><?= htmlspecialchars($eyebrow, ENT_QUOTES, 'UTF-8') ?></span></td>
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
