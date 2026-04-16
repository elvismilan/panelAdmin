# CLI Module Generator

Genera automáticamente todos los archivos de un módulo nuevo siguiendo los patrones del framework.

---

## Prerrequisito: Flujo de 2 pasos

El generador **solo crea archivos PHP**. El elemento del menú y sus permisos se crean desde el panel:

```
Paso 1 — Admin panel  →  /modulos/agregar  (registra el elemento en DB + asigna permisos)
Paso 2 — CLI          →  php bin/make-module.php ...  (genera los archivos PHP)
```

> El `ele_nombre` del panel debe coincidir exactamente con el `--route` del CLI.

---

## Uso

```bash
# Desde la raíz del proyecto
php bin/make-module.php [opciones]
```

### Modo interactivo (sin argumentos)
```bash
php bin/make-module.php
```
El script hace las preguntas paso a paso.

### Modo por flags
```bash
php bin/make-module.php --name=Producto --route=productos --prefix=pro --table=producto
```

---

## Parámetros

| Parámetro | Requerido | Descripción | Ejemplo |
|---|:---:|---|---|
| `--name` | ✓ | Nombre PascalCase singular | `Producto`, `OrdenVenta` |
| `--route` | ✓ | Ruta URL plural (minúsculas) | `productos`, `ordenes-venta` |
| `--prefix` | ✓ | Prefijo de columnas DB (2–5 letras) | `pro`, `ord`, `rpt` |
| `--table` | ✓ | Nombre de tabla sin prefijo | `producto`, `orden_venta` |
| `--fields` | — | Campos adicionales (ver formato abajo) | `pro_precio:decimal:10:2` |
| `--readonly` | — | Genera solo `index` + `ver` (sin CRUD) | flag |
| `--soft-deletes` | — | Agrega `deleted_at` al modelo y migración | flag |
| `--migration-num` | — | Número de migración (auto-detectado si se omite) | `0006` |
| `--dry-run` | — | Muestra los archivos sin crearlos | flag |
| `--force` | — | Sobreescribe archivos existentes | flag |

---

## Formato de campos (`--fields`)

```
col_nombre:tipo:tamaño[,col2:tipo:tamaño,...]
```

Para `decimal` el tamaño es `precision:escala`:
```
pro_precio:decimal:10:2
```

### Tipos disponibles

| Tipo | SQL generado | Regla validación | Input HTML |
|---|---|---|---|
| `string` | `VARCHAR(n) NOT NULL` | `required\|string\|min:2\|max:n` | `text` |
| `char` | `CHAR(n) NOT NULL DEFAULT ''` | `required\|string\|min:1\|max:n` | `text` |
| `text` | `TEXT NULL` | `nullable\|string` | `textarea` |
| `int` | `INT NOT NULL DEFAULT 0` | `required` | `number` |
| `decimal` | `DECIMAL(p,s) NOT NULL DEFAULT 0.00` | `required` | `number` |
| `date` | `DATE NULL` | `nullable` | `date` |
| `datetime` | `DATETIME NULL` | `nullable` | `datetime-local` |
| `bool` | `TINYINT(1) NOT NULL DEFAULT 0` | `nullable` | `checkbox` |

> Si no se especifican `--fields`, se genera automáticamente el campo `{pre}_nombre:string:250`.

---

## Archivos generados

### Módulo CRUD (por defecto)
```
app/Controllers/{Modulo}Controller.php     ← 7 acciones: index, agregar, guardar, editar, actualizar, eliminar, borrar
app/Models/{Modulo}Model.php               ← paginate, countAll, findById, createRecord, updateRecord, deleteRecord, existsByName
app/Views/{recurso}/index.php              ← listado con búsqueda y paginación
app/Views/{recurso}/agregar.php            ← formulario de creación
app/Views/{recurso}/editar.php             ← formulario de edición
app/Views/{recurso}/eliminar.php           ← confirmación de borrado
core/database/migrations/NNNN_create_{tabla}.sql       ← CREATE TABLE
core/database/migrations/NNNN_create_{tabla}_down.sql  ← DROP TABLE
```

### Módulo readonly (`--readonly`)
```
app/Controllers/{Modulo}Controller.php     ← 2 acciones: index, ver
app/Models/{Modulo}Model.php               ← paginate, countAll, findById
app/Views/{recurso}/index.php              ← listado con búsqueda y paginación
app/Views/{recurso}/ver.php                ← detalle completo del registro
core/database/migrations/...
```

---

## Ejemplos

### CRUD básico
```bash
php bin/make-module.php \
  --name=Categoria \
  --route=categorias \
  --prefix=cat \
  --table=categoria
```
Genera un módulo con el campo `cat_nombre:string:250` por defecto.

### CRUD con campos extra
```bash
php bin/make-module.php \
  --name=Producto \
  --route=productos \
  --prefix=pro \
  --table=producto \
  --fields="pro_nombre:string:250,pro_precio:decimal:10:2,pro_stock:int,pro_estado:char:1"
```

### CRUD con soft deletes
```bash
php bin/make-module.php \
  --name=Contrato \
  --route=contratos \
  --prefix=con \
  --table=contrato \
  --fields="con_numero:string:50,con_descripcion:text,con_fecha_inicio:date" \
  --soft-deletes
```

### Solo lectura (como el módulo de Logs)
```bash
php bin/make-module.php \
  --name=Reporte \
  --route=reportes \
  --prefix=rpt \
  --table=reporte \
  --readonly
```

### Ver sin crear (dry-run)
```bash
php bin/make-module.php --name=Producto --route=productos --prefix=pro --table=producto --dry-run
```

---

## Después de ejecutar el generador

El script imprime el bloque de rutas listo para copiar. Agrégalo a `routes/web.php`:

```php
use App\Controllers\ProductoController;

// Producto
$router->get( '/productos',                 ProductoController::class, 'index');
$router->get( '/productos/agregar',         ProductoController::class, 'agregar');
$router->post('/productos/guardar',         ProductoController::class, 'guardar');
$router->get( '/productos/{id}/editar',     ProductoController::class, 'editar');
$router->post('/productos/{id}/actualizar', ProductoController::class, 'actualizar');
$router->get( '/productos/{id}/eliminar',   ProductoController::class, 'eliminar');
$router->post('/productos/{id}/borrar',     ProductoController::class, 'borrar');
```

Luego ejecuta la migración:
```bash
php migrate.php
```

---

## Checklist post-generación

- [ ] Copiar bloque de rutas en `routes/web.php`
- [ ] Ejecutar `php migrate.php`
- [ ] Crear el elemento en `/modulos/agregar` con `ele_nombre` = `--route`
- [ ] Asignar tareas al elemento (ACCEDER, AGREGAR, EDITAR, ELIMINAR)
- [ ] Asignar permisos al grupo en `/grupos/{id}/editar`
- [ ] Revisar y ajustar los formularios generados según necesidades del negocio

---

## Notas

- El primer campo de `--fields` se usa como campo de búsqueda y validación de unicidad (`existsByName`).
- Los archivos existentes **no se sobreescriben** a menos que se use `--force`.
- El número de migración se auto-detecta leyendo el mayor `NNNN` en `core/database/migrations/`.
- La opción `--soft-deletes` no está disponible en modo `--readonly`.
