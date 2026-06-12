# PanelAdmin

Panel administrativo MVC en PHP 8.2+, orientado a hosting compartido, con autenticación sobre tablas reales (`usuario` + `persona`), RBAC por módulo/tarea y sistema de temas.

## Estado de esta documentación

Actualizada según el código del repositorio el **12 de junio de 2026**.

## Requisitos

- PHP 8.2 o superior
- Extensiones PHP: `pdo`, `gd`
- MySQL 5.7+ o MariaDB compatible
- Apache con `mod_rewrite`
- Composer

## Instalación rápida

1. Instalar dependencias:

```bash
composer install
```

2. Crear archivo de entorno:

```bash
cp .env.example .env
```

3. Configurar `.env` (mínimo):

- `APP_URL`
- `DB_HOST` + `DB_NAME` + `DB_USER` + `DB_PASS` (o `DB_DSN`)

4. Cargar esquema base:

- Importar `core/database/admin_db.sql` en tu base.

5. Ejecutar migraciones pendientes:

```bash
php migrate.php
```

## Comandos útiles

```bash
php migrate.php            # Aplica migraciones pendientes
php migrate.php status     # Estado de migraciones
php migrate.php rollback   # Revierte la última migración (si existe *_down.sql)

php bin/make-module.php    # Generador interactivo de módulos

php tests/reset_password_flow_smoke.php
```

## Estructura principal

```text
panelAdmin/
├── app/
│   ├── Controllers/
│   ├── Models/
│   └── Views/
├── core/
│   ├── database/
│   │   ├── admin_db.sql
│   │   └── migrations/
│   └── *.php
├── public/
│   ├── index.php
│   └── assets/
├── resources/themes/
├── routes/web.php
├── docs/
├── migrate.php
└── bin/make-module.php
```

## Módulos actualmente registrados en rutas

- `Auth`: `/login`, `/logout`, `/forgot-password`, `/reset-password/{token}`
- `Dashboard`: `/dashboard`
- `Tareas`: `/tareas`
- `Módulos/Elementos`: `/modulos`
- `Personas`: `/personas`
- `Usuarios`: `/usuarios`
- `Grupos`: `/grupos`
- `Logs`: `/logs`
- `Notificaciones`: `/notificaciones`
- `Parámetros`: `/parametros`

## Seguridad implementada (resumen)

- Tokens CSRF en formularios POST (`Core\Csrf`)
- Sesión endurecida (`httponly`, `samesite`, `secure` configurable)
- Rate limit de login/forgot por IP (`Core\RateLimiter`)
- Cabeceras HTTP de seguridad en `public/index.php` y `public/.htaccess`
- Permisos RBAC por grupo, elemento y tarea (`Core\Permission`)

## Sistema de temas

Áreas soportadas por `ThemeResolver`:

- `public`
- `login`
- `admin`

Variables de entorno principales:

- `PUBLIC_THEME_PACK`, `PUBLIC_THEME_OPTION`
- `LOGIN_THEME_PACK`, `LOGIN_THEME_OPTION`
- `ADMIN_THEME_PACK`, `ADMIN_THEME_OPTION`

## Documentación técnica

- Guía general del framework: [`docs/README.md`](docs/README.md)
- Generador CLI de módulos: [`docs/CLI-GENERATOR.md`](docs/CLI-GENERATOR.md)
- Plantilla de módulo: [`docs/MODULE_TEMPLATE.md`](docs/MODULE_TEMPLATE.md)
- Notificaciones: [`docs/NOTIFICACIONES.md`](docs/NOTIFICACIONES.md)
- Roadmap técnico de optimización: [`docs/ROADMAP-OPTIMIZACION.md`](docs/ROADMAP-OPTIMIZACION.md)

## Convención de tablas

- Las 15 tablas core del sistema usan prefijo fijo `wr_`.
- Las tablas de módulos de negocio existentes y futuros no usan prefijo.
- La estructura core activa se define por migraciones.
- La data actual del sistema puede conservarse importando un export aparte.
