# PanelAdmin Template

Plantilla base MVC en PHP 8 para hosting compartido, con autenticacion sobre tablas `wr_*`, permisos por `wr_elemento` + `wr_grupo`, y registro de acciones en `wr_logs`.

## Requisitos

- PHP 8.2+
- MySQL 5.7+
- Apache con mod_rewrite

## Instalacion

1. Instalar dependencias:

	composer install

2. Crear entorno local:

	cp .env.example .env

3. Ajustar credenciales en `.env`.

	 - Define `APP_URL` con la URL publica completa (incluye subcarpeta si aplica), por ejemplo:
		 - `APP_URL=https://tudominio.com/panel`
	 - `SITE_ROOT` se mantiene para compatibilidad y uso en correos.

4. Crear/importar tu propia base de datos segun el cliente.

5. Configurar tablas base de autenticacion y permisos con prefijo `wr_`:

- `wr_usuario`
- `wr_persona`
- `wr_grupo`
- `wr_elemento`
- `wr_permiso`
- `wr_logs`

## Estructura base

- `public/index.php`: front controller
- `routes/web.php`: rutas web
- `core/`: nucleo del framework
- `app/Controllers`: controladores PSR-4
- `app/Models`: modelos PSR-4
- `app/Views`: vistas base (fallback)
- `resources/themes`: templates por pack/area/opcion

## Themes por area

- `public`: template del sitio publico CMS
- `default`: template de login y dashboard administrador

Variables de entorno recomendadas:

- `PUBLIC_THEME_PACK=public`
- `LOGIN_THEME_PACK=default`
- `ADMIN_THEME_PACK=default`

## Seguridad y logs

- Login principal con `wr_usuario` y `wr_persona`
- Fallback opcional por variables `AUTH_USERNAME` y `AUTH_PASSWORD`
- Permisos por grupo/elemento desde `wr_permiso`
- Log de acciones en `wr_logs` mediante `logAction()` del controlador base

## Deploy en hosting compartido

- Opcion recomendada: apuntar el dominio a carpeta `public`
- Opcion alternativa: dejar dominio en raiz y usar `.htaccess` raiz para redirigir a `public`
- Si el panel se sirve dentro de subcarpeta o reverse proxy, configura `APP_URL` con esa ruta base para generar URLs absolutas correctas.
