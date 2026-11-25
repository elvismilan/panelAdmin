# Despliegue por FTP (lecciones del deploy de josePerales, 24/11/2026)

Un proyecto con el core como paquete se sube por FTP, sin SSH. Estos puntos evitan los dos problemas del primer deploy: un 500 porque `vendor/elvismilan` llegó incompleto y un login roto porque faltaba `resources/themes/admin/`.

## Antes de subir

1. **PHP 8.3.36 o superior en el hosting**, y comprobar que el sitio sigue andando. Con una versión menor, `vendor/composer/platform_check.php` detiene el sitio con un error fatal.
2. **Respaldo** de la base (phpMyAdmin → Exportar → Personalizado, todas las tablas) y de los archivos que se van a reemplazar.
3. **`.env` de producción:** `APP_DEBUG=false`; `APP_URL` con esquema y dominio reales, sin barra final (es la única fuente de URLs absolutas: sin ella todas las páginas dan 500 "Sitio sin configurar"); una sola forma de la base (`DB_DSN` o `DB_HOST` + `DB_NAME`); `TRUST_PROXY=true` solo detrás de un proxy HTTPS.

## `vendor/`

1. En local, con el PHP 8.3 de MAMP:
   ```bash
   /Applications/MAMP/bin/php/php8.3.0/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader
   ```
2. Subir `vendor/` **completo** (incluye `vendor/composer/` y `vendor/elvismilan/paneladmin-core/`; sin `.git`). En un sitio que ya existe, súbelo como `vendor_v1/` y renómbralo al final: `vendor/` → `vendor_old/` y `vendor_v1/` → `vendor/`.
3. **Verificar en el servidor** que `vendor/elvismilan/paneladmin-core/` y `vendor/composer/` estén completos (cuenta de archivos y tamaño) antes de probar.
4. Al terminar, en local: `composer install` otra vez (con las dependencias de desarrollo).

## Archivos del proyecto

Subir, con la misma ruta: `app/`, `resources/themes/admin/` (con `login/` y `errors/`), `routes/web.php`, `public/index.php`, `public/.htaccess`, `public/assets/` (theme-one, `paneladmin/` y `app/` —de `assets:publish` y los tokens—, public-theme y js), `composer.json` y `composer.lock`.

No se suben: `tests/`, `docs/`, `bin/`, `phpstan.neon`, `README.md`, `.env.example` ni `database/` (las migraciones se corren en phpMyAdmin). `public/uploads/` solo la primera vez.

Después de cada `composer update` del core: `vendor/bin/paneladmin assets:publish` en local y subir `vendor/` y `public/assets/paneladmin/`.

## SQL

Primero el SQL, después el código. Sin SSH, el SQL de las migraciones se pega en phpMyAdmin, con juego de caracteres **utf-8**. Con una base nueva: el de `vendor/elvismilan/paneladmin-core/database/migrations/` (en orden) y el de `database/migrations/` (en orden), y los registros de `wr_migrations` (`migrate --baseline --sql` los imprime para una base que ya tiene las tablas del core). El primer administrador: `vendor/bin/paneladmin admin:create --sql`.

## Después de subir

- Volver a iniciar sesión (las sesiones viejas se pierden).
- Probar: login, Dashboard, un módulo con imagen, el sitio público y el envío de Contacto.
- Que `/core/`, `/vendor/` y `/.env` no se puedan abrir.
- Sin avisos ni trazas de PHP en el HTML.
- Al día siguiente, si todo anda: borrar `vendor_old/`.
