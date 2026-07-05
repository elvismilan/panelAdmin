# Notificaciones — Email e internas

Documentación actual del sistema de notificaciones y envíos de correo del panel.

## Estado de esta guía

Validada contra el código del repositorio el **12 de junio de 2026**.

## 1. Correo saliente (`Core\Mailer`)

`Core\Mailer` es un wrapper sobre PHPMailer y usa variables SMTP desde `.env`.

Variables necesarias:

```ini
MAIL_HOST=mail.tudominio.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=correo@dominio.com
MAIL_PASSWORD=app_password
MAIL_FROM_ADDRESS=correo@dominio.com
MAIL_FROM_NAME=Panel Admin
```

Uso base:

```php
use Core\Mailer;

$mailer = new Mailer();
$mailer->send($destinatario, $asunto, $html);
```

## 2. Render de plantillas de email

Las plantillas se renderizan con `Core\EmailTemplatesRenderer`, disponible en controladores como `$this->emailTemplatesRenderer`.

Ejemplo real:

```php
$emailHtml = $this->emailTemplatesRenderer->render(
    \Core\EmailMessages::TEMPLATE_PASSWORD_RESET,
    [
        'resetUrl'      => $resetUrl,
        'userName'      => $userName,
        'siteTitle'     => $siteTitle,
        'address'       => (string) ($_ENV['ADDRESS'] ?? ''),
        'country'       => (string) ($_ENV['COUNTRY'] ?? ''),
        'expiryMinutes' => 60,
    ]
);
```

Plantillas existentes (`app/Views/emails/`):

- `password-reset.php`
- `usuario-credenciales.php` (histórica/disponible, no es la plantilla principal del flujo actual de alta)

## 3. Alta de usuario y email enviado

Implementado en `UsuarioController::guardar()`.

Comportamiento actual:

- Se crea el usuario.
- Si el usuario está vinculado a `persona` y tiene `per_email`, se genera token de reset.
- Se envía email con **enlace de configuración de contraseña** (no contraseña en texto plano).
- Si falla el correo, se registra en `error_log` y el alta del usuario se mantiene.

Flujo simplificado:

```text
POST /usuarios/guardar
  -> UsuarioModel::createUsuario()
  -> UsuarioModel::getPersonaEmail($usuId)
  -> PasswordResetModel::createToken($email)
  -> EmailTemplatesRenderer::render('emails/password-reset', ...)
  -> Mailer::send(...)
```

## 4. Recuperación de contraseña

Implementado en `AuthController::processForgotPassword()`.

Flujo:

```text
POST /forgot-password
  -> rate limit por IP
  -> PasswordResetModel::findUserByEmail()
  -> PasswordResetModel::createToken()
  -> EmailTemplatesRenderer::render('emails/password-reset', ...)
  -> Mailer::send(...)
```

Detalles:

- Token de recuperación con expiración (60 minutos).
- Respuesta neutral al usuario para no revelar si el correo existe.

## 5. Notificaciones internas del panel

### Servicio

`Core\NotificacionService::registrar(...)` registra eventos para mostrar en UI.

Firma actual:

```php
NotificacionService::registrar(
    string $modulo,
    string $accion,
    string $usuOrigen,
    ?string $referenciaId = null,
    ?string $mensajeExtra = null,
    array|string|null $destinos = null
): void
```

Destinos soportados:

```php
// Global para todos los usuarios autenticables
NotificacionService::registrar('usuarios', 'CREATE', 'admin');

// Usuario unico
NotificacionService::registrar('usuarios', 'CREATE', 'admin', 'USR-1', null, 'juan');

// Varios usuarios
NotificacionService::registrar('usuarios', 'CREATE', 'admin', 'USR-1', null, ['juan', 'ana']);

// Mezcla de usuarios y grupos
NotificacionService::registrar('usuarios', 'CREATE', 'admin', 'USR-1', null, [
    'usuarios' => ['juan', 'ana'],
    'grupos' => ['Administrador', 'Caja'],
]);
```

Notas:

- Los grupos se expanden a usuarios activos en el momento de crear la notificacion.
- `wr_notificacion_destino` y `wr_notificacion_lectura` forman parte del esquema oficial activo.
- `wr_notificacion_destino` controla visibilidad.
- `wr_notificacion_lectura` controla estado de lectura por usuario.
- Si una notificacion se crea sin destinatarios explicitos, se considera global y queda visible por compatibilidad mediante ausencia de filas en `wr_notificacion_destino`.

### Tipos por acción

- `CREATE` -> `success`
- `UPDATE` -> `warning`
- `DELETE` -> `danger`

### Triggers activos detectados

- `UsuarioController`: `CREATE`, `UPDATE`, `DELETE`
- `PersonaController`: `CREATE`, `UPDATE`, `DELETE`
- `GrupoController`: `CREATE`, `UPDATE`, `DELETE`
- `ElementoController`: `CREATE`, `UPDATE`, `DELETE`
- `TareaController`: `CREATE`, `UPDATE`, `DELETE`
- `ParametroController`: `CREATE`, `UPDATE`, `DELETE`

Cobertura actual:

- `usuarios`, `personas`, `grupos`, `modulos`, `tareas` y `parametros` cubren `CREATE`, `UPDATE`, `DELETE`

### Visualización en UI

- Dropdown del header: `Core\Helpers\NotificationHelper` (últimas 5 no leídas)
- Listado: `GET /notificaciones`
- Detalle: `GET /notificaciones/{id}/ver` (auto-marca leída)
- Marcado explícito: `POST /notificaciones/{id}/leida`
- Estas rutas son internas y no requieren alta en `elemento`; el acceso se resuelve por autenticación.

### Modelo de datos

`NotificacionModel` trabaja sobre el esquema oficial:

- `wr_notificacion`: evento generado por el sistema.
- `wr_notificacion_destino`: usuarios que pueden verla.
- `wr_notificacion_lectura`: marca de lectura por usuario (`nrl_usu_id`).
- Si una notificación no tiene destinatarios registrados, se trata como global por compatibilidad.

## 6. Migraciones relacionadas

- `0004_create_wr_notification_tables.sql`

Ejecutar:

```bash
php migrate.php
```

## 7. Plan de WhatsApp (pendiente)

La integración WhatsApp Business API sigue como plan futuro y **no está implementada** en el estado actual del código.

Sugerencia de implementación futura:

- `core/WhatsApp.php`
- extensión de `UsuarioController::guardar()` tras envío de email
- variables `.env` para token, phone id y template
