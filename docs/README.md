# panelAdmin — Documentación técnica

Guía actual del proyecto para desarrollo, operación y onboarding.

## Estado de esta guía

Actualizada el **25 de noviembre de 2026**. panelAdmin consume `elvismilan/paneladmin-core` `^1.0` por Composer (no tiene carpeta `core/`) y es la **plantilla** de las webs nuevas: pasos en [NUEVO-PROYECTO.md](NUEVO-PROYECTO.md) y de despliegue en [DESPLIEGUE.md](DESPLIEGUE.md).

## 1. Resumen del sistema

- Arquitectura MVC en PHP 8.2+.
- Front controller único: `public/index.php`.
- Router propio con rutas declaradas en `routes/web.php`.
- RBAC por `grupo` + `elemento` + `tarea`.
- Multi-tema por área (`public`, `login`, `admin`) mediante `ThemeResolver`.
- Solo las 15 tablas core usan prefijo fijo `wr_`.
- Las tablas de modulos de negocio existentes y los nuevos modulos usan nombres sin prefijo.

## 2. Estructura actual del proyecto

```text
panelAdmin/
├── app/
│   ├── Controllers/      módulos de contenido
│   │                     (Blogs, Contenidos, Redes, Slider, Galerías, Servicios, Testimonios)
│   │                     + sitio público (Home, Blog, Contacto, Servicio, Seo)
│   ├── Messages/         textos de negocio (Flash, Ui, Validation)
│   ├── Models/
│   └── Views/
├── database/migrations/  migraciones del proyecto (el core trae las suyas, core/000N)
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── assets/           theme-one (admin), paneladmin (pantallas del core), app/admin.css (tokens), public-theme (sitio), js
├── resources/
│   └── themes/admin/     layout, header/footer, login y errores del admin
├── routes/
│   └── web.php
├── tests/
├── bin/
│   └── make-module.php
├── vendor/               incluye vendor/elvismilan/paneladmin-core (el core)
└── .env.example
```

## 3. Módulos y rutas registradas

### Módulos principales

| Módulo | Controller | Modelo(s) | Ruta base | FilterBar |
|---|---|---|---|---|
| Admin (login, Dashboard, Usuarios, Personas, Grupos, Módulos, Tareas, Logs, Parámetros, Notificaciones) | **del core** (`PanelAdmin\Core\Admin\Controllers`) | del core | `/login`, `/dashboard`, `/usuarios`… | — |
| Home | `HomeController` | `SliderModel`, `ServicioModel`, `BlogsModel`, `TestimonioModel` | `/` | — |
| Blog (+categorías) | `BlogsController` | `BlogsModel`, `CategoriaModel` | `/blogs` | ✅ |
| Contenidos | `ContenidosController` | `ContenidosModel` | `/contenidos` | ✅ |
| Redes sociales | `RedesSocialesController` | `RedesSocialesModel` | `/redes` | ❌ |
| Slider | `SliderController` | `SliderModel` | `/slider` | ❌ |
| Galerías | `GaleriaController` | `GaleriaModel` | `/galerias` | ❌ |
| Servicios | `ServicioController` | `ServicioModel` | `/servicios` | ❌ |
| Testimonios | `TestimonioController` | `TestimonioModel` | `/testimonios` | ❌ |
| Sitio público | `BlogController`, `ContactoController`, `ServicioPublicController`, `SeoController` | `BlogModel`, `ContactoModel` | `/blog`, `/contacto`, `/servicio/{slug}`, `/sitemap.xml`, `/robots.txt` | — |

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
  -> PanelAdmin\Core\App::run()
  -> PanelAdmin\Core\Router::dispatch()
  -> App\Controllers\...::accion()
  -> App\Models\...
  -> PanelAdmin\Core\Controller::render() / renderAdminModule()
  -> PanelAdmin\Core\View
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
- Envío de email con `PanelAdmin\Core\Mailer` + plantilla `app/Views/emails/password-reset.php`.
- Tokens con caducidad y uso único.

## 6. RBAC y menú dinámico

### Permisos

`PanelAdmin\Core\Permission` resuelve acceso por:

- grupo (`pmo_gru_id`)
- elemento (`pmo_ele_id`)
- tarea (`pmo_tar_id` / `tarea.tar_nombre`)

Optimización actual:

- cachea en sesión y por request la resolución `ruta -> elemento`
- cachea en sesión y por request la matriz de permisos por grupo
- invalida el cache RBAC actual al cambiar grupos, módulos o tareas
- revalida la versión global RBAC desde `wr_parametro` cada `30s` por defecto (`RBAC_VERSION_CHECK_INTERVAL`)

`Router::isForbidden()` deniega rutas cuando el usuario autenticado no tiene permiso.

### Menú

`PanelAdmin\Core\MenuService::buildForGroup($groupId)` construye el árbol visible según permisos y lo cachea en sesión (`_menu_cache`).

## 7. Temas y layouts

Resolución de vistas por área vía `PanelAdmin\Core\ThemeResolver`.

### Archivos del admin

```text
resources/themes/admin/
├── layout.php
├── header-admin.php
├── footer-admin.php
├── auth-layout-header.php
├── auth-layout-footer.php
├── login/{login,forgot-password,reset-password}.php
└── errors/{forbidden,not-found}.php
```

Las pantallas del admin son las del core (`vendor/elvismilan/paneladmin-core/resources/views/`): una vista en `app/Views/<pantalla>/` la reemplaza. El `ThemeResolver` del core busca primero en `resources/themes/admin/` del proyecto, después en el del paquete y por último en `app/Views/`. Los estilos y scripts del admin son los de `public/assets/theme-one/`. El sitio público usa `app/Views/shared/` y `public/assets/public-theme/`.

## 8. Configuración `.env` (realmente usada)

### App y URLs

- `APP_DEBUG` - Modo debug (true|false)
- `APP_URL` - URL base de la aplicación
- `APP_INDEX` - **Página de inicio por defecto** (`'home'` | `'login'`) — **Nuevo 11 jun 2026**
- `SITE_TITLE` - Título del sitio
- `LOGO` - Ruta del logo

### Base de datos

- `DB_DSN` (opcional, tiene prioridad)
- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `DB_CHARSET`

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
- `ADMIN_*`, `LOGIN_*` y `SITE_ROOT` ya no se leen (core 1.0).

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
vendor/bin/paneladmin migrate
vendor/bin/paneladmin status
vendor/bin/paneladmin rollback
```

### Migraciones

- **Del core** (en el paquete, `core/0001` a `core/0008`): tablas `wr_*`, RBAC por defecto (grupo `Administrador`, sección `adm`) y `parametros`.
- **Del proyecto** (`database/migrations/`):
  - `0001_create_content_tables`: `categorias`, `categoria_relacion`, `posts`, `contenidos`, `servicios`, `slider`, `galeria`, `galeria_foto`, `redes_sociales`.
  - `0002_create_testimonios_table` y `0003_create_contactos_table`.
  - `0004_seed_website_elementos`: sección Website, módulos de contenido, tareas `FOTOS` y `ORDENAR` y permisos de Administrador.

Base nueva: `vendor/bin/paneladmin migrate` y `vendor/bin/paneladmin admin:create`.

## 11. Notificaciones

El módulo usa `NotificacionModel` con visibilidad por destinatario y lectura por usuario.

- `wr_notificacion` guarda el evento.
- `wr_notificacion_destino` guarda a qué usuarios se entrega cada notificación.
- `wr_notificacion_lectura` guarda quién ya la leyó (`nrl_usu_id`, `nrl_leida_en`).
- `wr_notificacion_destino` y `wr_notificacion_lectura` son parte del esquema oficial activo.
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
- [`ROADMAP-OPTIMIZACION.md`](ROADMAP-OPTIMIZACION.md)
