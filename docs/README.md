# panelAdmin — Documentación técnica

Guía actual del proyecto para desarrollo, operación y onboarding.

## Estado de esta guía

Validada contra el código del repositorio el **26 de mayo de 2026**.

## 1. Resumen del sistema

- Arquitectura MVC en PHP 8.2+.
- Front controller único: `public/index.php`.
- Router propio con rutas declaradas en `routes/web.php`.
- RBAC por `grupo` + `elemento` + `tarea`.
- Multi-tema por área (`public`, `login`, `admin`) mediante `ThemeResolver`.
- Soporte de tablas con prefijo dinámico (`DB_PREFIX`) para tablas core.

## 2. Estructura actual del proyecto

```text
panelAdmin/
├── app/
│   ├── Controllers/
│   ├── Models/
│   └── Views/
├── core/
│   ├── Helpers/
│   ├── database/
│   │   ├── admin_db.sql
│   │   └── migrations/
│   ├── App.php
│   ├── Router.php
│   ├── Controller.php
│   ├── Model.php
│   └── ...
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── assets/
├── resources/
│   └── themes/
├── routes/
│   └── web.php
├── tests/
│   └── reset_password_flow_smoke.php
├── bin/
│   └── make-module.php
├── migrate.php
└── .env.example
```

## 3. Módulos y rutas registradas

### Módulos principales

| Módulo | Controller | Modelo(s) | Ruta base |
|---|---|---|---|
| Home | `HomeController` | — | `/` |
| Auth | `AuthController` | `UserModel`, `PasswordResetModel` | `/login` |
| Dashboard | `DashboardController` | — | `/dashboard` |
| Tareas | `TareaController` | `TareaModel` | `/tareas` |
| Módulos/Elementos | `ElementoController` | `ElementoModel` | `/modulos` |
| Personas | `PersonaController` | `PersonaModel` | `/personas` |
| Usuarios | `UsuarioController` | `UsuarioModel` | `/usuarios` |
| Grupos | `GrupoController` | `GrupoModel` | `/grupos` |
| Logs | `LogController` | `LogModel` | `/logs` |
| Notificaciones | `NotificacionController` | `NotificacionModel` | `/notificaciones` |

### Rutas especiales (no CRUD estándar)

- `GET /login`
- `POST /login`
- `POST /logout`
- `GET /forgot-password`
- `POST /forgot-password`
- `GET /reset-password/{token}`
- `POST /reset-password/{token}`
- `GET /logs/{id}/ver`
- `GET /notificaciones/{id}/ver`
- `POST /notificaciones/{id}/leida`

### Patrón CRUD usado en módulos administrativos

Para `tareas`, `modulos`, `personas`, `usuarios`, `grupos`:

- `GET /{recurso}` → `index`
- `GET /{recurso}/agregar` → `agregar`
- `POST /{recurso}/guardar` → `guardar`
- `GET /{recurso}/{id}/editar` → `editar`
- `POST /{recurso}/{id}/actualizar` → `actualizar`
- `GET /{recurso}/{id}/eliminar` → `eliminar`
- `POST /{recurso}/{id}/borrar` → `borrar`

## 4. Flujo MVC (request → response)

```text
Request HTTP
  -> public/index.php
  -> Core\App::run()
  -> Core\Router::dispatch()
  -> App\Controllers\...::accion()
  -> App\Models\...
  -> Core\Controller::render() / renderAdminModule()
  -> Core\View
  -> HTML
```

Notas importantes:

- `Request` normaliza método, path y soporta override por `POST _method`.
- `Request` elimina el `basePath` detectado desde `APP_URL`/script path.
- `Router` renderiza errores usando templates:
  - `resources/themes/default/errors/not-found.php` (404)
  - `resources/themes/default/errors/forbidden.php` (403)

## 5. Autenticación y recuperación de contraseña

### Login

`AuthController::login()` aplica:

- validación CSRF
- rate limit por IP (`RateLimiter`)
- autenticación por `UserModel`
- `Auth::login()` con regeneración de sesión

### Logout

`POST /logout` exige CSRF y destruye sesión (`Auth::logout()`).

### Forgot / Reset Password

- Generación de token por `PasswordResetModel`.
- Envío de email con `Core\Mailer` + plantilla `app/Views/emails/password-reset.php`.
- Tokens con caducidad y uso único.

## 6. RBAC y menú dinámico

### Permisos

`Core\Permission` resuelve acceso por:

- grupo (`pmo_gru_id`)
- elemento (`pmo_ele_id`)
- tarea (`pmo_tar_id` / `tarea.tar_nombre`)

`Router::isForbidden()` deniega rutas cuando el usuario autenticado no tiene permiso.

### Menú

`Core\MenuService::buildForGroup($groupId)` construye el árbol visible según permisos y lo cachea en sesión (`_menu_cache`).

## 7. Temas y layouts

Resolución de vistas por área vía `Core\ThemeResolver`.

### Archivos actuales de tema

```text
resources/themes/default/
├── admin/layout.php
├── admin/dashboard-default.php
├── login/login-form-default.php
├── login/forgot-password.php
├── login/reset-password.php
├── errors/forbidden.php
├── errors/not-found.php
├── header-admin.php
├── footer-admin.php
└── auth-layout-*.php

resources/themes/public/public/
├── option1.php
└── option2.php
```

Notas:

- Para `login` y `admin`, la opción efectiva está forzada a `1` en `ThemeResolver`.
- `renderAdminModule()` usa el layout `admin/layout.php` del pack activo.

## 8. Configuración `.env` (realmente usada)

### App y URLs

- `APP_DEBUG`
- `APP_URL`
- `SITE_ROOT`
- `SITE_TITLE`
- `LOGO`

### Base de datos

- `DB_DSN` (opcional, tiene prioridad)
- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `DB_CHARSET`
- `DB_PREFIX` (opcional)

### Autenticación y seguridad

- `AUTH_ACTIVE_STATUS`
- `AUTH_LEGACY_ALGO`
- `AUTH_LEGACY_SALT`
- `LOGIN_MAX_ATTEMPTS` (default: `5`)
- `LOGIN_LOCKOUT_MINUTES` (default: `15`)
- `CSRF_TOKEN_TTL_SECONDS` (default: `3600`)
- `SESSION_COOKIE_SECURE` (`auto|true|false`)
- `SESSION_COOKIE_SAMESITE` (`Lax|Strict|None`)
- `SESSION_COOKIE_DOMAIN`

### UI / Paginación / Tema

- `PAGINATION_PER_PAGE`
- `PUBLIC_THEME_PACK`, `PUBLIC_THEME_OPTION`
- `LOGIN_THEME_PACK`, `LOGIN_THEME_OPTION`
- `ADMIN_THEME_PACK`, `ADMIN_THEME_OPTION`
- `LOGIN_OPTION1_ASSET_BASE`
- `LOGIN_ASSET_BASE`
- `ADMIN_ASSET_BASE`
- `ADMIN_META_DESCRIPTION`, `ADMIN_META_KEYWORDS`, `ADMIN_META_AUTHOR`

### Correo y contacto

- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_ENCRYPTION`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`
- `COUNTRY`
- `ADDRESS`

### Testing

- `TEST_RESET_EMAIL`

## 9. Seguridad implementada

- PDO con sentencias preparadas.
- CSRF token por sesión, con TTL configurable.
- Sesiones con `httponly`, `samesite` y `secure` configurable.
- Rate limiting para login/forgot password.
- Headers de seguridad en `public/index.php` y `public/.htaccess`.
- CSP activa (actualmente permite `unsafe-inline` para compatibilidad de tema).

## 10. Migraciones y esquema

### Runner de migraciones

Comandos:

```bash
php migrate.php
php migrate.php status
php migrate.php rollback
```

### Migraciones existentes

- `0001_create_login_attempts.sql`
- `0002_add_soft_deletes.sql`
- `0003_create_password_resets.sql`
- `0004_create_notificacion.sql`
- `0005_create_notificacion_destino.sql`
- `0006_create_notificacion_lectura.sql`
- `0007_backfill_notificacion_lectura.sql`

### Base inicial

`core/database/admin_db.sql` contiene el esquema base del panel (catálogos/tablas núcleo).

## 11. Notificaciones

El módulo usa `NotificacionModel` con visibilidad por destinatario y lectura por usuario.

- `wr_notificacion` guarda el evento.
- `wr_notificacion_destino` guarda a qué usuarios se entrega cada notificación.
- `wr_notificacion_lectura` guarda quién ya la leyó (`nrl_usu_id`, `nrl_leida_en`).
- Si una notificación no tiene filas en `wr_notificacion_destino`, se interpreta como global por compatibilidad.
- Las rutas `/notificaciones*` son internas: requieren sesión, pero no dependen de `elemento` ni aparecen en el menú RBAC.

Ruta de consulta para usuario autenticado:

- `GET /notificaciones`
- `GET /notificaciones/{id}/ver` (marca leída al abrir)
- `POST /notificaciones/{id}/leida`

## 12. Utilidades CLI

### Generador de módulos

- Script: `bin/make-module.php`
- Guía: [`docs/CLI-GENERATOR.md`](CLI-GENERATOR.md)
- Plantilla extendida: [`docs/MODULE_TEMPLATE.md`](MODULE_TEMPLATE.md)

### Smoke test de reset password

```bash
php tests/reset_password_flow_smoke.php
```

Valida:

- consumo de token válido
- bloqueo de reutilización
- rechazo de token expirado

## 13. Documentos relacionados

- [`../README.md`](../README.md)
- [`CLI-GENERATOR.md`](CLI-GENERATOR.md)
- [`MODULE_TEMPLATE.md`](MODULE_TEMPLATE.md)
- [`NOTIFICACIONES.md`](NOTIFICACIONES.md)

