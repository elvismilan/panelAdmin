# Notificaciones — Email y WhatsApp

Documentación del sistema de notificaciones del panel: envío de correos transaccionales y plan de integración con WhatsApp.

---

## Tabla de contenidos

1. [Core\Mailer](#1-coremailer)
2. [Plantillas de email](#2-plantillas-de-email)
3. [Envío de credenciales al crear usuario](#3-envío-de-credenciales-al-crear-usuario)
4. [Recuperación de contraseña](#4-recuperación-de-contraseña)
5. [Plan: WhatsApp Business API (pendiente)](#5-plan-whatsapp-business-api-pendiente)

---

## 1. Core\Mailer

Clase wrapper sobre **PHPMailer**. Lee su configuración del `.env`.

### Variables de entorno requeridas

```ini
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=correo@dominio.com
MAIL_PASSWORD=app_password
MAIL_FROM_ADDRESS=correo@dominio.com
MAIL_FROM_NAME=Panel Admin
```

### Uso

```php
use Core\Mailer;
use PHPMailer\PHPMailer\Exception as MailerException;

try {
    $html   = $this->renderEmailView('emails/mi-plantilla', $data);
    $mailer = new Mailer();
    $mailer->send($destinatario, 'Asunto del correo', $html);
} catch (MailerException $e) {
    error_log('Mailer error: ' . $e->getMessage());
}
```

### `renderEmailView(string $template, array $data): string`

Método disponible en `Core\Controller`. Renderiza una vista de `app/Views/` como string HTML sin layout de admin.

---

## 2. Plantillas de email

Ubicación: `app/Views/emails/`

| Archivo | Descripción | Variables |
|---|---|---|
| `password-reset.php` | Enlace para restablecer contraseña | `$resetUrl`, `$userName`, `$siteTitle`, `$address`, `$country`, `$expiryMinutes` |
| `usuario-credenciales.php` | Credenciales de acceso para nuevo usuario | `$userName`, `$usuId`, `$password`, `$loginUrl`, `$siteTitle`, `$address`, `$country` |

### Crear una nueva plantilla

1. Copiar `password-reset.php` como base
2. Declarar las variables con `htmlspecialchars` al inicio del archivo
3. Construir el HTML inline (los clientes de email no soportan CSS externo)
4. Registrar en esta tabla

---

## 3. Envío de credenciales al crear usuario

### Comportamiento

Al guardar un nuevo usuario en `UsuarioController::guardar()`, si la persona vinculada tiene `per_email` registrado, se le envía automáticamente un correo con sus datos de acceso.

- Si el usuario **no tiene persona vinculada** → no se envía correo
- Si la persona vinculada **no tiene email** → no se envía correo
- Si el correo **falla** → se registra en `error_log` pero el usuario se crea igual

### Flujo

```
POST /usuarios/guardar
  ↓
Validación de formulario
  ↓
UsuarioModel::createUsuario()
  ↓
UsuarioModel::getPersonaEmail($usuId)  ← JOIN usuario + persona, filtra per_email vacío
  ↓ (solo si hay email)
renderEmailView('emails/usuario-credenciales', [...])
  ↓
Mailer::send($email, 'Tus credenciales...', $html)
  ↓
flashSuccess() + redirect('/usuarios')
```

### Métodos involucrados

| Clase | Método | Descripción |
|---|---|---|
| `UsuarioModel` | `getPersonaEmail(string $usuId): ?string` | Retorna el email de la persona vinculada o `null` |
| `UsuarioController` | `guardar(): void` | Orquesta creación + envío de correo |

---

## 4. Recuperación de contraseña

Implementado en `AuthController::processForgotPassword()`.

### Flujo

```
POST /forgot-password
  ↓
PasswordResetModel::createToken($email)   ← token único con expiración
  ↓
renderEmailView('emails/password-reset', [...])
  ↓
Mailer::send($email, 'Recuperación de contraseña...', $html)
```

El token expira en **60 minutos**. Si el email no existe en el sistema se muestra igual el mensaje de éxito para no revelar información.

---

## 5. Plan: WhatsApp Business API (pendiente)

Integración futura para enviar credenciales también por WhatsApp al crear un usuario, usando `per_telefono` de la tabla `persona`.

### Proveedor elegido

**Meta WhatsApp Cloud API** — sin SDK externo, solo HTTP requests.

Endpoint:
```
POST https://graph.facebook.com/v19.0/{PHONE_ID}/messages
Authorization: Bearer {TOKEN}
```

### Variables de entorno a agregar

```ini
WHATSAPP_TOKEN=EAAxxxxxxxxxxxxxxx
WHATSAPP_PHONE_ID=1234567890
WHATSAPP_TEMPLATE_NAME=bienvenida_credenciales
WHATSAPP_TEMPLATE_LANG=es
```

### Pasos para habilitar

#### 1. Configurar cuenta en Meta

1. Ir a [developers.facebook.com](https://developers.facebook.com) → crear app tipo **Business**
2. Agregar producto **WhatsApp** a la app
3. En **Getting Started** obtener `WHATSAPP_TOKEN` y `WHATSAPP_PHONE_ID`
4. Para producción: verificar el negocio en Meta Business Manager

#### 2. Crear plantilla de mensaje aprobada

Los mensajes que inician conversación requieren plantillas aprobadas por Meta.

Ruta: Meta Business Suite → WhatsApp → Plantillas de mensaje → Crear

```
Nombre:     bienvenida_credenciales
Categoría:  UTILITY
Idioma:     es

Cuerpo:
Hola {{1}}, tu cuenta en {{2}} ha sido creada.
Usuario: {{3}}
Contraseña: {{4}}
Ingresa en: {{5}}
```

Parámetros: `[nombre, siteTitle, usuId, password, loginUrl]`

> La aprobación puede tardar entre minutos y 24 horas.

#### 3. Archivos a crear / modificar

| Archivo | Acción | Descripción |
|---|---|---|
| `core/WhatsApp.php` | Crear | Cliente HTTP para Meta Cloud API |
| `app/Models/UsuarioModel.php` | Modificar | Agregar `getPersonaTelefono(string $usuId): ?string` |
| `app/Controllers/UsuarioController.php` | Modificar | Integrar envío WA en `guardar()` tras el bloque de email |
| `.env` | Modificar | Agregar las 4 variables de WhatsApp |

#### 4. Estructura de `Core\WhatsApp`

```php
namespace Core;

class WhatsApp
{
    public function sendTemplate(
        string $to,           // Número en formato internacional sin '+' (ej: 59170000000)
        string $templateName,
        string $languageCode,
        array  $bodyParams    // Parámetros posicionales del cuerpo
    ): void

    public function sendCredentials(
        string $telefono,
        string $userName,
        string $usuId,
        string $password
    ): void
}
```

#### 5. Normalización del número de teléfono

`per_telefono` puede estar guardado sin código de país (ej: `70000000`).  
Antes de enviar se debe anteponer el código del país configurado en `.env`:

```ini
PHONE_COUNTRY_CODE=591
```

#### 6. Comportamiento esperado (igual que email)

- Si no hay persona vinculada → no enviar
- Si `per_telefono` está vacío → no enviar
- Si falla el envío → `error_log`, el usuario se crea igual

---

---

## 6. Notificaciones internas del sistema

Notificaciones en tiempo real dentro del panel. Son **solo lectura** y se generan automáticamente desde los controllers. No se crean manualmente.

Se muestran en:
- **Header admin** → dropdown de la campana (últimas 5 no leídas)
- **`/notificaciones`** → listado completo con filtros
- **`/notificaciones/{id}/ver`** → detalle; al abrir se marca como leída automáticamente

### Agregar un trigger en cualquier controller

**Paso 1** — importar el servicio:

```php
use Core\NotificacionService;
```

**Paso 2** — llamar después del `logAction`, pasando módulo, acción, usuario y el ID del registro:

```php
$model->createRegistro($params);
$this->logAction($model->getLastActionLog(), 'CREATE');
NotificacionService::registrar('modulo', 'CREATE', (string) (Auth::user()['id'] ?? 'ANON'), $id);
```

### Firma completa

```php
NotificacionService::registrar(
    string  $modulo,        // 'usuarios' | 'personas' | 'grupos' | 'modulos' | 'tareas'
    string  $accion,        // 'CREATE' | 'UPDATE' | 'DELETE'
    string  $usuOrigen,     // ID del usuario que ejecutó la acción
    ?string $referenciaId,  // ID del registro afectado (null si no aplica)
    ?string $mensajeExtra   // Texto adicional opcional al final del mensaje
);
```

### Acciones y tipos visuales

| Acción   | Tipo      | Color   |
|----------|-----------|---------|
| `CREATE` | `success` | Verde   |
| `UPDATE` | `warning` | Amarillo|
| `DELETE` | `danger`  | Rojo    |

### Módulos disponibles

Definidos en `ETIQUETA_MODULO` dentro de `core/NotificacionService.php`. Para registrar uno nuevo añadirlo ahí:

```php
private const ETIQUETA_MODULO = [
    'usuarios'   => 'Usuarios',
    'personas'   => 'Personas',
    'nuevo_modulo' => 'Mi Módulo',   // ← agregar aquí
];
```

### Triggers activos

| Controller          | Método    | Acción   |
|---------------------|-----------|----------|
| `UsuarioController` | `guardar` | `CREATE` |

### Migración

```bash
php migrate.php   # crea notificacion si aun no existe
```

### Archivos clave

| Archivo | Descripción |
|---|---|
| `core/NotificacionService.php` | Servicio estático — punto de entrada para registrar |
| `app/Models/NotificacionModel.php` | Acceso a BD: `createRecord`, `countNoLeidas`, `marcarLeida` |
| `app/Controllers/NotificacionController.php` | Rutas: index, ver (auto-marca leída), marcarLeida |
| `core/Helpers/NotificationHelper.php` | Renderiza el dropdown del header |
| `core/database/migrations/0004_create_notificacion.sql` | Estructura de la tabla |

---

*Ver también: [README.md](README.md) — documentación general del framework.*
