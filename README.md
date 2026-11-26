# PanelAdmin (plantilla)

Plantilla de las webs nuevas: admin MVC en PHP 8.3+ con login, RBAC por módulo y tarea, módulos de contenido (Blog, Contenidos, Redes, Slider, Galerías, Servicios, Testimonios, Contacto) y un sitio público mínimo. El core (`elvismilan/paneladmin-core` `^1.0`) se instala por Composer y vive en `vendor/`; este repo solo tiene el código del proyecto.

- **Crear una web nueva desde aquí:** [`docs/NUEVO-PROYECTO.md`](docs/NUEVO-PROYECTO.md)
- **Subir a producción:** [`docs/DESPLIEGUE.md`](docs/DESPLIEGUE.md)

## Requisitos

- PHP 8.3 o superior (en MAMP: `/Applications/MAMP/bin/php/php8.3.0/bin/php`; la CLI 8.2 no instala el paquete)
- Extensiones: `pdo`, `gd`, `mbstring`, `ctype`
- MySQL 5.7+ o MariaDB compatible
- Apache con `mod_rewrite`
- Composer, y acceso SSH a GitHub (el core es un repositorio privado y se instala desde el código fuente)

## Instalación

```bash
cp .env.example .env        # APP_URL, DB_* y lo demás que haga falta
composer install            # con el PHP 8.3
vendor/bin/paneladmin assets:publish   # CSS y JS de las pantallas del core (public/assets/paneladmin)
vendor/bin/paneladmin migrate
vendor/bin/paneladmin admin:create --group=Developer
```

`migrate` aplica primero las migraciones del core (`core/0001` a `core/0009`, que cargan el RBAC por defecto y el grupo oculto `Developer`) y después las del proyecto (`database/migrations/`). Después se entra en `/login`.

## Comandos

```bash
vendor/bin/paneladmin migrate        # aplica las migraciones pendientes
vendor/bin/paneladmin status         # estado de las migraciones
vendor/bin/paneladmin rollback       # revierte la última del proyecto (rollback --core, la del core)
vendor/bin/paneladmin admin:create   # crea un usuario (--group=Developer para el primero)

composer phpstan                     # análisis estático (nivel 5)
composer test                        # tests
composer verify                      # phpstan + tests

php bin/make-module.php              # generador de módulos
```

## Estructura

```text
├── app/                  módulos de contenido y sitio público (Controllers, Models, Views, Messages)
├── database/migrations/  migraciones del proyecto
├── public/               index.php, .htaccess, uploads y assets: theme-one (admin), paneladmin (pantallas del core,
│                         de assets:publish), app/admin.css (ajustes propios), public-theme (sitio) y js
├── resources/themes/admin/  layout, header, footer, login y errores del admin con theme-one
├── routes/web.php
├── tests/
├── bin/make-module.php
└── docs/
```

## Módulos y rutas

- Admin (pantallas del **core**; mejoran con `composer update` y `assets:publish`): `/login`, `/dashboard`, `/usuarios`, `/personas`, `/grupos`, `/modulos`, `/tareas`, `/logs`, `/parametros`, `/notificaciones`
- Contenido (Website): `/blogs`, `/contenidos`, `/redes`, `/slider`, `/galerias`, `/servicios`, `/testimonios`
- Sitio público: `/`, `/blog`, `/blog/{slug}`, `/servicio/{slug}`, `/contacto`, `/sitemap.xml`, `/robots.txt`

## Seguridad (resumen)

- CSRF en los formularios POST, sesión endurecida, rate limit del login.
- Cabeceras de seguridad y CSP en `public/index.php` y `public/.htaccess`.
- Permisos por grupo, elemento y tarea; `APP_DEBUG=false` en producción.

## Documentación

- [`docs/README.md`](docs/README.md): guía técnica
- [`docs/CLI-GENERATOR.md`](docs/CLI-GENERATOR.md), [`docs/MODULE_TEMPLATE.md`](docs/MODULE_TEMPLATE.md), [`docs/NOTIFICACIONES.md`](docs/NOTIFICACIONES.md)
- Del core: `vendor/elvismilan/paneladmin-core/README.md` y `UPGRADE.md`

## Convención de tablas

Las tablas del core llevan el prefijo `wr_`; las del negocio no llevan prefijo.
