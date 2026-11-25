# Crear una web nueva desde la plantilla

panelAdmin es la plantilla: cada web nueva sale de aquí, sin `core/` (el core viene de Composer).

## Qué trae la plantilla

- **Admin completo** con theme-one: login, recuperación de clave, Dashboard, Usuarios, Personas, Grupos, Módulos, Tareas, Bitácora, Parámetros y Notificaciones. **Las pantallas son las del core** (no hay copias en `app/`): las mejoras llegan con `composer update`. El proyecto aporta el layout, el header, el footer y el login con theme-one (`resources/themes/admin/`) y los tokens de color `--pa-*` (`public/assets/app/admin.css`).
- **Módulos de contenido** (sección Website): Blog con categorías, Contenidos, Redes sociales, Slider, Galerías, Servicios y Testimonios.
- **Sitio público mínimo:** Inicio, Blog, detalle de Servicio, Contacto, `sitemap.xml` y `robots.txt`.
- **Migraciones** de esas tablas y de sus permisos (`database/migrations/0001` a `0004`).
- `.env.example` con lo necesario y `docs/DESPLIEGUE.md`.

## Pasos

Desde la carpeta de proyectos (`/Volumes/Blackbox/Projects`), con `NOMBRE` el del proyecto nuevo:

1. **Copiar** sin `.git`, `vendor/`, `.env`, `composer.lock` ni las imágenes subidas:
   ```bash
   rsync -a --exclude '.git' --exclude 'vendor' --exclude '.env' --exclude 'composer.lock' \
     --exclude 'public/uploads/*' --exclude '.DS_Store' panelAdmin/ NOMBRE/
   mkdir -p NOMBRE/public/uploads && cp panelAdmin/public/uploads/.htaccess NOMBRE/public/uploads/
   ```
2. **Nombre y datos:** en `composer.json` cambia `name` y `description`; en `.env`, `SITE_TITLE`.
3. **Git:** `cd NOMBRE && git init -b develop`. Los commits van siempre en `develop`.
4. **`.env`:** `cp .env.example .env` y completa `APP_URL` (la URL real, sin barra final; en local, la del vhost, por ejemplo `https://nombre.dev:8890`), la base (`DB_DSN` **o** `DB_HOST` + `DB_NAME`, no las dos), `DB_USER`, `DB_PASS`, `SITE_TITLE` y el correo. `APP_DEBUG=false` siempre en producción.
5. **Dependencias**, con el PHP 8.3 de MAMP (el core se baja por SSH desde el repositorio privado):
   ```bash
   /Applications/MAMP/bin/php/php8.3.0/bin/php /usr/local/bin/composer install
   ```
6. **CSS y JS de las pantallas del core:**
   ```bash
   /Applications/MAMP/bin/php/php8.3.0/bin/php vendor/bin/paneladmin assets:publish
   ```
   (copia `paneladmin.css`, `paneladmin.js` y el favicon a `public/assets/paneladmin/`). Se repite después de cada `composer update` del core.
7. **Base de datos:** crea la base vacía y corre
   ```bash
   /Applications/MAMP/bin/php/php8.3.0/bin/php vendor/bin/paneladmin migrate
   /Applications/MAMP/bin/php/php8.3.0/bin/php vendor/bin/paneladmin admin:create
   ```
   `admin:create` pide los datos y la contraseña en la terminal; sin SSH, `admin:create --sql` imprime el SQL para phpMyAdmin.
8. **Quitar lo que la web no use.** Para sacar un módulo: borra su línea en `routes/web.php`, su controlador, su modelo, sus vistas (`app/Views/<modulo>/`) y sus mensajes en `app/Messages/`; y borra su elemento en Módulos (o con una migración nueva). Las tablas de `0001` pueden quedarse.
9. **Sitio público:** cambia `app/Views/shared/` (header, footer, layout), `app/Views/home/index.php` y `public/assets/public-theme/` (CSS, logos y favicon: `img/logo-*.svg` y `img/favicon.png` son de ejemplo).
10. **Módulos nuevos del negocio:** `php bin/make-module.php` (ver [CLI-GENERATOR.md](CLI-GENERATOR.md)). Sus migraciones siguen la numeración de `database/migrations/` (desde `0005`).
11. **Comprobar:** `composer verify` (PHPStan y tests) con el PHP 8.3, recorrer el admin con Administrador y con un grupo de pocos permisos (un 403 significa que falta el elemento o el permiso).
12. **Primer commit:** `git add .` y `git commit -m "chore: proyecto inicial desde la plantilla panelAdmin"`.

## Cosas a tener en cuenta

- Para cambiar una pantalla del admin en un proyecto, copia su vista del core (`vendor/elvismilan/paneladmin-core/resources/views/<pantalla>/`) a `app/Views/<pantalla>/`: la del proyecto gana, pero deja de recibir las mejoras. Mejor pedir el cambio al core.
- El color y el modo oscuro/glass: los tokens `--pa-*` están en `public/assets/app/admin.css`; el selector de colores de theme-one sigue cargando `color-N.css` y no cambia esos tokens.
- El logo del admin es `public/assets/public-theme/img/logo-blue.svg`.
- Las tablas de contenido usan `utf8mb4_unicode_ci` e ids `INT`.
- `public/uploads/` no se versiona; en producción se sube a mano la primera vez.
