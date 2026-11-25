<?php
/**
 * Footer compartido de los emails transaccionales: cierra el body/tablas
 * abiertas por emails/shared/header.php y agrega la linea de pie de pagina.
 *
 * Variables esperadas: $siteTitle, $address, $country.
 */
$siteTitle = (string) ($siteTitle ?? 'Mi Sitio');
$address = (string) ($address ?? '');
$country = (string) ($country ?? '');
?>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Footer -->
          <table style="width:650px; margin:24px auto 0 auto">
            <tbody>
              <tr>
                <td class="text-center" style="color:#999; font-size:12px">
                  <?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8') ?><?= $address !== '' ? ' · ' . htmlspecialchars($address, ENT_QUOTES, 'UTF-8') : '' ?><?= $country !== '' ? ' · ' . htmlspecialchars($country, ENT_QUOTES, 'UTF-8') : '' ?>
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
