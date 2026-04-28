# panelAdmin — Documentación del Framework

Panel de administración personalizado en PHP MVC, diseñado para hosting compartido, con RBAC, multi-tema y arquitectura limpia.

---

## Tabla de contenidos

1. [Estructura del proyecto](#1-estructura-del-proyecto)
2. [Módulos existentes](#2-módulos-existentes)
3. [Arquitectura MVC](#3-arquitectura-mvc)
4. [Sistema de autenticación](#4-sistema-de-autenticación)
5. [RBAC — Roles y permisos](#5-rbac--roles-y-permisos)
6. [Sistema de temas](#6-sistema-de-temas)
7. [Clases base](#7-clases-base)
8. [Utilidades del framework](#8-utilidades-del-framework)
9. [Convenciones de nomenclatura](#9-convenciones-de-nomenclatura)
10. [Configuración (.env)](#10-configuración-env)
11. [Seguridad implementada](#11-seguridad-implementada)
12. [Migraciones](#12-migraciones)

---

## 1. Estructura del proyecto

```
panelAdmin/
├── public/                      # Web root — único punto de entrada
│   ├── index.php                # Front controller
│   └── assets/                  # CSS, JS, imágenes públicas
├── app/
│   ├── Controllers/             # Controladores de cada módulo
│   ├── Models/                  # Modelos de cada módulo
│   └── Views/
│       ├── {modulo}/            # Vistas por módulo (index, agregar, editar, eliminar)
│       └── components/          # Componentes reutilizables
├── core/                        # Framework — NO modificar salvo extensión
│   ├── App.php
│   ├── Controller.php
│   ├── Model.php
│   ├── Router.php
│   ├── Request.php
│   ├── Auth.php
│   ├── Permission.php
│   ├── Validator.php
│   ├── Csrf.php
│   ├── Session.php
│   ├── View.php
│   ├── MenuService.php
│   ├── ThemeResolver.php
│   ├── ActionLogger.php
│   ├── RateLimiter.php
│   ├── Mailer.php
│   ├── Helpers/
│   │   └── IconHelper.php
│   └── database/
│       └── migrations/          # Archivos SQL de migración
├── resources/
│   └── themes/
│       ├── default/             # Tema por defecto (admin + login)
│       └── public/              # Tema área pública
├── routes/
│   └── web.php                  # Registro de todas las rutas
├── vendor/                      # Dependencias Composer
├── app/.env                     # Configuración del entorno
└── migrate.php                  # Runner de migraciones
```

---

## 2. Módulos existentes

| Módulo | Controlador | Modelo | Ruta base | Tabla(s) DB |
|---|---|---|---|---|
| **Auth** | `AuthController` | `UserModel`, `PasswordResetModel` | `/login`, `/logout`, `/forgot-password`, `/reset-password` | `usuario`, `login_attempts`, `password_resets` |
| **Dashboard** | `DashboardController` | — | `/dashboard` | — |
| **Tarea** | `TareaController` | `TareaModel` | `/tareas` | `tarea` |
| **Elemento (Módulo)** | `ElementoController` | `ElementoModel` | `/modulos` | `elemento`, `elemento_tarea` |
| **Persona** | `PersonaController` | `PersonaModel` | `/personas` | `persona` |
| **Usuario** | `UsuarioController` | `UsuarioModel` | `/usuarios` | `usuario`, `persona`, `grupo` |
| **Grupo** | `GrupoController` | `GrupoModel` | `/grupos` | `grupo` |

Todos los módulos CRUD implementan el mismo conjunto de 7 acciones:

| Acción | Método HTTP | Ruta | Descripción |
|---|---|---|---|
| `index` | GET | `/{recurso}` | Listado con búsqueda y paginación |
| `agregar` | GET | `/{recurso}/agregar` | Formulario de creación |
| `guardar` | POST | `/{recurso}/guardar` | Procesa la creación |
| `editar` | GET | `/{recurso}/{id}/editar` | Formulario de edición |
| `actualizar` | POST | `/{recurso}/{id}/actualizar` | Procesa la edición |
| `eliminar` | GET | `/{recurso}/{id}/eliminar` | Confirmación de borrado |
| `borrar` | POST | `/{recurso}/{id}/borrar` | Ejecuta el borrado |

---

## 3. Arquitectura MVC

### Flujo de una petición

```
Browser HTTP Request
  ↓
public/index.php
  ↓  Carga .env, cabeceras de seguridad
Core\App::run()
  ↓
Core\Router::dispatch()
  ↓  Encuentra ruta, extrae parámetros, verifica permisos
App\Controllers\{Modulo}Controller::{accion}()
  ↓  requireAuth(), validación, lógica de negocio
App\Models\{Modulo}Model
  ↓  Consultas PDO preparadas
Core\Controller::renderAdminModule('modulo/vista', $data)
  ↓
Core\View::render()  →  extract($data)
  ↓
resources/themes/default/admin/layout.php
  ├── header-admin.php
  ├── app/Views/modulo/vista.php
  └── footer-admin.php
  ↓
Response HTML al browser
```

### Registro de rutas (`routes/web.php`)

```php
// Rutas simples
$router->get('/ruta', ControllerClass::class, 'metodo');
$router->post('/ruta', ControllerClass::class, 'metodo');

// Rutas con parámetros
$router->get('/recurso/{id}/editar', RecursoController::class, 'editar');
$router->post('/recurso/{id}/actualizar', RecursoController::class, 'actualizar');
```

---

## 4. Sistema de autenticación

### Flujo de login

1. `GET /login` → `AuthController::showLogin()` — muestra formulario
2. `POST /login` → `AuthController::login()`
   - Rate limiting via `RateLimiter` (5 intentos / 15 min por IP)
   - `UserModel::authenticate()` — verifica credenciales (bcrypt + HMAC legacy)
   - `Auth::login($user)` — crea sesión y regenera ID
3. `POST /logout` → `AuthController::logout()` — destruye sesión

### Datos en sesión tras login

```php
[
    'id'         => int,       // ID del usuario
    'username'   => string,
    'person_id'  => int,       // ID de persona vinculada
    'group'      => int,       // ID del grupo (para RBAC)
    'group_name' => string,
    'full_name'  => string,
    'email'      => string,
    'photo'      => string,    // Ruta de foto
    'auth_driver'=> 'wr_usuario',
]
```

### Recuperación de contraseña

```
GET  /forgot-password              → Formulario de email
POST /forgot-password              → Genera token, envía email (PHPMailer)
GET  /reset-password/{token}       → Formulario de nueva contraseña
POST /reset-password/{token}       → Actualiza contraseña y limpia token
```

---

## 5. RBAC — Roles y permisos

### Modelo de datos

```
grupo (Grupos/Roles)
  └── permiso (grupo → elemento → tarea)
        ├── elemento (Módulos del menú)
        │     └── elemento_tarea (elemento → tarea)
        └── tarea (Acciones: ACCEDER, LISTAR, AGREGAR, EDITAR, ELIMINAR, etc.)
```

### Verificación de permisos en vistas

```php
$permission = new \Core\Permission();
$canAgregar  = $permission->canAccessRoute($groupId, '/recurso/agregar', 'agregar');
$canEditar   = $permission->canAccessRoute($groupId, '/recurso/1/editar', 'editar');
$canEliminar = $permission->canAccessRoute($groupId, '/recurso/1/eliminar', 'eliminar');
```

### Menú dinámico

`Core\MenuService::buildForGroup($groupId)` genera el árbol de menú solo con los elementos a los que el grupo tiene acceso. El resultado se cachea en sesión (`_menu_cache`).

---

## 6. Sistema de temas

### Configuración en `.env`

```
ADMIN_THEME_PACK=default
ADMIN_THEME_OPTION=1
LOGIN_THEME_PACK=default
LOGIN_THEME_OPTION=1
```

### Estructura de un tema

```
resources/themes/{pack}/
├── admin/
│   ├── layout.php              # Layout principal del admin
│   ├── dashboard-default.php   # Opción 1 del dashboard
│   └── (solo opción default)
├── login/
│   ├── login-form-default.php
│   ├── forgot-password.php
│   └── reset-password.php
├── errors/
│   ├── 404.php
│   └── 403.php
├── header-admin.php
└── footer-admin.php
```

---

## 7. Clases base

### `Core\Controller`

Todo controlador hereda de esta clase.

```php
// Seguridad
$this->requireAuth();                          // Redirige a /login si no autenticado
$this->requireCsrf();                          // Valida token CSRF (en POST)
$this->requireElementPermission($elementId);   // Verifica permiso RBAC

// Renderizado
$this->renderAdminModule('modulo/vista', $data); // Con layout admin
$this->render('template', $data);               // Renderizado directo
$this->json($data, 200);                        // Respuesta JSON

// Navegación
$this->redirect('/ruta');

// Flash messages
$this->flashSuccess('Mensaje de éxito.');
$this->flashError('Mensaje de error.');

// Paginación
$page     = $this->getCurrentPage();
$perPage  = $this->getDefaultPerPage();         // Lee PAGINATION_PER_PAGE del .env
$pagination = $this->buildPagination($total, $page, $perPage, '/ruta', $queryParams);

// Parámetros de query
$filters = $this->getQueryParams(['q' => '', 'estado' => '']);
$value   = $this->getQueryParam('q', '');

// Log de acciones
$this->logAction('Descripción', 'CREATE|UPDATE|DELETE');

// Assets
$this->addCss('/assets/css/extra.css');
$this->addJs('/assets/js/extra.js');
```

### `Core\Model`

Todo modelo hereda de esta clase.

```php
// Constructor mínimo
public function __construct() {
    parent::__construct();
    $this->setTable('nombre_tabla');   // Sin prefijo
    $this->setPrimaryKey('col_id');
}

// CRUD heredado
$this->create(['col' => $val]);        // INSERT → retorna ID como string
$this->find($id);                      // SELECT por PK
$this->findAll();                      // SELECT todos
$this->update($id, ['col' => $val]);   // UPDATE por PK
$this->delete($id);                    // DELETE (o soft delete si habilitado)
$this->restore($id);                   // Restaura soft-deleted

// Helpers de validación
$this->existsIn($tabla, $columna, $valor, $excluirCol, $excluirVal); // Unicidad
$this->linkedTo($tabla, $columna, $valor);                           // FK activa

// Soft deletes (opcional)
protected bool $softDeletes = true;    // Activa borrado lógico (deleted_at)

// Log del último INSERT/UPDATE/DELETE
$this->getLastActionLog();
```

---

## 8. Utilidades del framework

### Validator

```php
$validator = Validator::make($params, [
    'campo'  => 'required|string|min:2|max:250',
    'estado' => 'required|in:A,I',
    'notas'  => 'nullable|string|max:500',
], [
    'campo'  => 'Nombre del Campo',
    'estado' => 'Estado',
]);

if ($validator->fails()) {
    $error  = $validator->first();   // Primer error
    $errors = $validator->errors();  // Todos los errores
}

$valor = $validator->value('campo', 'default');
```

Reglas disponibles: `required`, `string`, `min:{n}`, `max:{n}`, `in:{a,b}`, `regex:{pattern}`, `nullable`.

### CSRF

```php
// En la vista (dentro de <form>):
<?= $csrfField ?>   // Inyectado automáticamente por renderAdminModule

// En el controlador (POST):
$this->requireCsrf();
```

### Session

```php
Session::set('key', $value);
Session::get('key', $default);
Session::has('key');
Session::remove('key');
Session::destroy();
Session::regenerateId();
```

### Auth

```php
Auth::check();       // bool — ¿está autenticado?
Auth::user();        // array — datos del usuario en sesión
Auth::login($user);  // Crea sesión
Auth::logout();      // Destruye sesión
```

### Componentes de vistas reutilizables

| Archivo | Uso | Variables requeridas |
|---|---|---|
| `components/flash-messages.php` | Alertas de éxito/error | `$flashes` (inyectado automático) |
| `components/form-errors.php` | Errores de validación | `$error`, `$errors` |
| `components/pagination.php` | Controles de paginación | `$pagination`, `$paginationAriaLabel`, `$paginationClass` |
| `components/search-form.php` | Formulario de búsqueda | `$searchConfig` (array de configuración) |
| `components/image-upload.php` | Upload de imágenes | configuración inline |

---

## 9. Convenciones de nomenclatura

| Elemento | Convención | Ejemplo |
|---|---|---|
| Controlador | `{Modulo}Controller.php` — PascalCase | `ProductoController.php` |
| Modelo | `{Modulo}Model.php` — PascalCase | `ProductoModel.php` |
| Carpeta de vistas | minúsculas | `app/Views/producto/` |
| Archivos de vista | minúsculas | `index.php`, `agregar.php` |
| Tabla DB | `{prefijo}{modulo}` — minúsculas | `wr_producto` |
| Columnas DB | `{prefijo3}_{nombre}` — snake_case | `pro_nombre`, `pro_estado` |
| Clave primaria | `{prefijo3}_id` | `pro_id` |
| Rutas URL | plural minúsculas | `/productos` |
| Namespace controladores | `App\Controllers` | |
| Namespace modelos | `App\Models` | |

---

## 10. Configuración (.env)

```ini
# App
APP_DEBUG=false
APP_URL=https://dominio.com/panel
SITE_ROOT=https://dominio.com
SITE_TITLE=Mi Panel
LOGO=/assets/images/logo.png

# Base de datos
DB_HOST=localhost
DB_NAME=nombre_db
DB_USER=usuario
DB_PASS=contraseña
DB_CHARSET=utf8mb4
DB_PREFIX=wr_

# Auth
AUTH_ACTIVE_STATUS=H
AUTH_LEGACY_ALGO=sha256
AUTH_LEGACY_SALT=salt_secreto

# Paginación
PAGINATION_PER_PAGE=8

# Temas
ADMIN_THEME_PACK=default
ADMIN_THEME_OPTION=1
LOGIN_THEME_PACK=default
LOGIN_THEME_OPTION=1

# Mail (PHPMailer)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=correo@dominio.com
MAIL_PASSWORD=password
MAIL_FROM_ADDRESS=correo@dominio.com
MAIL_FROM_NAME=Panel Admin
```

---

## 11. Seguridad implementada

| Amenaza | Mecanismo |
|---|---|
| SQL Injection | PDO con sentencias preparadas en todas las consultas |
| XSS | `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')` en todas las vistas |
| CSRF | Token de sesión en formularios POST + `requireCsrf()` en controladores |
| Sesión hijacking | `session_regenerate_id(true)` en cada login |
| Fuerza bruta | Rate limiting: 5 intentos / 15 min por IP (`login_attempts`) |
| Contraseñas | bcrypt (`password_hash` / `password_verify`) |
| Clickjacking | Header `X-Frame-Options: SAMEORIGIN` |
| MIME sniffing | Header `X-Content-Type-Options: nosniff` |
| Referrer leak | Header `Referrer-Policy: strict-origin-when-cross-origin` |
| Scripts inline | Header `Content-Security-Policy` |

---

## 12. Migraciones

### Convención de archivos

```
core/database/migrations/
├── NNNN_descripcion.sql          # Migración UP
└── NNNN_descripcion_down.sql     # Rollback DOWN
```

### Ejecutar migraciones

```bash
php migrate.php          # Aplica todas las pendientes
php migrate.php rollback # Revierte la última
```

### Ejemplo de migración

```sql
-- 0005_create_producto.sql
CREATE TABLE IF NOT EXISTS `{prefix}producto` (
    `pro_id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `pro_nombre` VARCHAR(250) NOT NULL,
    `pro_estado` CHAR(1)      NOT NULL DEFAULT 'A',
    `pro_created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`pro_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 13. Catalogos de mensajes

Para reducir duplicacion de textos, el proyecto centraliza mensajes en clases dentro de `core/`:

- `FlashMessages.php`: mensajes de feedback (success, warning, error).
- `ValidationMessages.php`: mensajes de validacion y errores de formulario.
- `LogMessages.php`: mensajes/prefijos para `error_log`.
- `EmailMessages.php`: asuntos y rutas de templates de correo.
- `UiMessages.php`: titulos de pantalla reutilizables.

Recomendacion: antes de agregar un texto nuevo en un controlador, revisar si corresponde ubicarlo en alguno de estos catalogos.

---

## 14. Smoke test de reset de contraseña

Se agrego un test minimo ejecutable para validar el flujo critico de restablecimiento:

`tests/reset_password_flow_smoke.php`

Casos verificados:

1. Consumo exitoso de token valido.
2. Bloqueo de reutilizacion del mismo token.
3. Rechazo de token expirado.

Variable requerida en `.env`:

`TEST_RESET_EMAIL=correo_existente_en_wr_persona`

Ejecucion:

```bash
php tests/reset_password_flow_smoke.php
```

---

*Generado automáticamente — ver [MODULE_TEMPLATE.md](MODULE_TEMPLATE.md) para crear nuevos módulos.*
