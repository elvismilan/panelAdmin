# Template para generación de nuevos módulos

Cuando pidas crear un nuevo módulo, proporciona los siguientes datos y se generarán todos los archivos automáticamente siguiendo el estándar del framework.

---

## Datos requeridos para generar un módulo

```
Nombre del módulo  : {Modulo}          (ej: Producto)
Nombre plural URL  : {recursos}        (ej: productos)
Prefijo de columnas: {pre}             (ej: pro)
Tabla DB           : {tabla}           (ej: producto)
Campos del modelo  : ver sección abajo
```

---

## Archivos generados por módulo

```
app/
├── Controllers/{Modulo}Controller.php
├── Models/{Modulo}Model.php
└── Views/{recurso}/
    ├── index.php
    ├── agregar.php
    ├── editar.php
    └── eliminar.php

core/database/migrations/
├── NNNN_create_{tabla}.sql
└── NNNN_create_{tabla}_down.sql

routes/web.php    ← Se agregan 7 rutas al final
```

---

## 1. Controlador — `app/Controllers/{Modulo}Controller.php`

```php
<?php

namespace App\Controllers;

use App\Models\{Modulo}Model;
use Core\Auth;
use Core\Controller;
use Core\Validator;
use Throwable;

class {Modulo}Controller extends Controller
{
    // -------------------------------------------------------------------------
    // INDEX — Listado con búsqueda y paginación
    // -------------------------------------------------------------------------
    public function index(): void
    {
        $this->requireAuth();

        $user    = Auth::user();
        $items   = [];
        $page    = $this->getCurrentPage();
        $perPage = $this->getDefaultPerPage();
        $filters = $this->getQueryParams(['q' => '']);
        $search  = (string) ($filters['q'] ?? '');
        $pagination = $this->buildPagination(0, $page, $perPage, '/{recursos}', ['q' => $search]);

        try {
            $model      = new {Modulo}Model();
            $totalRows  = $model->countAll($search);
            $pagination = $this->buildPagination($totalRows, $page, $perPage, '/{recursos}', ['q' => $search]);
            $items      = $model->paginate((int) $pagination['offset'], (int) $pagination['perPage'], $search);
        } catch (Throwable) {
            $items = [];
        }

        $this->renderAdminModule('{recurso}/index', [
            'title'      => '{TituloPlural}',
            'user'       => $user,
            '{recurso}s' => $items,
            'pagination' => $pagination,
            'search'     => $search,
            'searchConfig' => [
                'action'      => '/{recursos}',
                'method'      => 'GET',
                'fields'      => [
                    [
                        'name'        => 'q',
                        'type'        => 'text',
                        'placeholder' => 'Buscar por nombre...',
                        'value'       => $search,
                        'icon'        => 'fa fa-search',
                    ],
                ],
                'submitLabel' => 'Buscar',
                'submitIcon'  => 'fa fa-search',
                'clearUrl'    => $search !== '' ? '/{recursos}' : '',
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // AGREGAR — Muestra formulario de creación
    // -------------------------------------------------------------------------
    public function agregar(): void
    {
        $this->requireAuth();

        $this->renderAdminModule('{recurso}/agregar', [
            'title' => 'Nuevo {titulo}',
            'user'  => Auth::user(),
            'error' => null,
            'form'  => [
                '{pre}_nombre' => '',
                // Agregar más campos según el modelo
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // GUARDAR — Procesa la creación
    // -------------------------------------------------------------------------
    public function guardar(): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model  = new {Modulo}Model();
        $params = $this->request->getParams();

        $validator = Validator::make($params, [
            '{pre}_nombre' => 'required|string|min:2|max:250',
            // Agregar más reglas según los campos
        ], [
            '{pre}_nombre' => 'Nombre',
        ]);

        $passes = $validator->passes();
        $nombre = (string) $validator->value('{pre}_nombre', '');

        if (!$passes) {
            $this->renderAdminModule('{recurso}/agregar', [
                'title'  => 'Nuevo {titulo}',
                'user'   => Auth::user(),
                'error'  => $validator->first(),
                'errors' => $validator->errors(),
                'form'   => ['{pre}_nombre' => $nombre],
            ]);
            return;
        }

        if ($model->existsByName($nombre)) {
            $this->renderAdminModule('{recurso}/agregar', [
                'title' => 'Nuevo {titulo}',
                'user'  => Auth::user(),
                'error' => 'Ya existe un registro con ese nombre.',
                'form'  => ['{pre}_nombre' => $nombre],
            ]);
            return;
        }

        $model->createRecord(['{pre}_nombre' => $nombre]);
        $this->logAction($model->getLastActionLog(), 'CREATE');
        $this->flashSuccess('{TituloSingular} creado correctamente.');
        $this->redirect('/{recursos}');
    }

    // -------------------------------------------------------------------------
    // EDITAR — Muestra formulario de edición
    // -------------------------------------------------------------------------
    public function editar(string $id): void
    {
        $this->requireAuth();

        $item = (new {Modulo}Model())->findById($id);
        if (!is_array($item)) {
            $this->redirect('/{recursos}');
            return;
        }

        $this->renderAdminModule('{recurso}/editar', [
            'title'      => 'Editar {titulo}',
            'user'       => Auth::user(),
            'error'      => null,
            'form'       => $item,
            '{recurso}Id' => $id,
        ]);
    }

    // -------------------------------------------------------------------------
    // ACTUALIZAR — Procesa la edición
    // -------------------------------------------------------------------------
    public function actualizar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model  = new {Modulo}Model();
        $params = $this->request->getParams();

        $validator = Validator::make($params, [
            '{pre}_nombre' => 'required|string|min:2|max:250',
        ], [
            '{pre}_nombre' => 'Nombre',
        ]);

        $passes = $validator->passes();
        $nombre = (string) $validator->value('{pre}_nombre', '');

        if (!$passes) {
            $this->renderAdminModule('{recurso}/editar', [
                'title'      => 'Editar {titulo}',
                'user'       => Auth::user(),
                'error'      => $validator->first(),
                'errors'     => $validator->errors(),
                'form'       => ['{pre}_nombre' => $nombre],
                '{recurso}Id' => $id,
            ]);
            return;
        }

        if ($model->existsByName($nombre, $id)) {
            $this->renderAdminModule('{recurso}/editar', [
                'title'      => 'Editar {titulo}',
                'user'       => Auth::user(),
                'error'      => 'Ya existe un registro con ese nombre.',
                'form'       => ['{pre}_nombre' => $nombre],
                '{recurso}Id' => $id,
            ]);
            return;
        }

        $model->updateRecord($id, ['{pre}_nombre' => $nombre]);
        $this->logAction($model->getLastActionLog(), 'UPDATE');
        $this->flashSuccess('{TituloSingular} actualizado correctamente.');
        $this->redirect('/{recursos}');
    }

    // -------------------------------------------------------------------------
    // ELIMINAR — Confirmación de borrado
    // -------------------------------------------------------------------------
    public function eliminar(string $id): void
    {
        $this->requireAuth();

        $model = new {Modulo}Model();
        $item  = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/{recursos}');
            return;
        }

        $this->renderAdminModule('{recurso}/eliminar', [
            'title'      => 'Eliminar {titulo}',
            'user'       => Auth::user(),
            'form'       => $item,
            '{recurso}Id' => $id,
        ]);
    }

    // -------------------------------------------------------------------------
    // BORRAR — Ejecuta el borrado
    // -------------------------------------------------------------------------
    public function borrar(string $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $model = new {Modulo}Model();
        $item  = $model->findById($id);
        if (!is_array($item)) {
            $this->redirect('/{recursos}');
            return;
        }

        // Opcional: verificar FK antes de borrar
        // if ($model->isLinkedTo{OtroModulo}($id)) {
        //     $this->flashError('No se puede eliminar porque está vinculado a otros registros.');
        //     $this->redirect('/{recursos}/' . urlencode($id) . '/eliminar');
        //     return;
        // }

        $model->deleteRecord($id);
        $this->logAction($model->getLastActionLog(), 'DELETE');
        $this->flashSuccess('{TituloSingular} eliminado correctamente.');
        $this->redirect('/{recursos}');
    }
}
```

---

## 2. Modelo — `app/Models/{Modulo}Model.php`

```php
<?php

namespace App\Models;

use Core\Model;

class {Modulo}Model extends Model
{
    private string ${recurso}Table;

    public function __construct()
    {
        parent::__construct();
        $this->{recurso}Table = $this->tableName('{tabla}');
        $this->setTable('{tabla}');
        $this->setPrimaryKey('{pre}_id');
        // $this->softDeletes = true; // Descomentar si se quiere borrado lógico
    }

    // -------------------------------------------------------------------------
    // Paginación con búsqueda
    // -------------------------------------------------------------------------
    public function paginate(int $offset, int $limit, string $search = ''): array
    {
        $sql = "SELECT {pre}_id, {pre}_nombre FROM {$this->{recurso}Table}";
        if ($search !== '') {
            $sql .= " WHERE {pre}_nombre LIKE :search";
        }
        $sql .= " ORDER BY {pre}_id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->getConnection()->prepare($sql);
        if ($search !== '') {
            $stmt->bindValue(':search', '%' . $search . '%', \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit',  max(1, $limit),  \PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // -------------------------------------------------------------------------
    // Conteo total para paginación
    // -------------------------------------------------------------------------
    public function countAll(string $search = ''): int
    {
        $sql    = "SELECT COUNT(*) AS total FROM {$this->{recurso}Table}";
        $params = [];

        if ($search !== '') {
            $sql .= " WHERE {pre}_nombre LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        $row = $this->db->query($sql, $params)->fetch();
        return (int) ($row['total'] ?? 0);
    }

    // -------------------------------------------------------------------------
    // Buscar por ID
    // -------------------------------------------------------------------------
    public function findById(string $id): ?array
    {
        $sql = "SELECT {pre}_id, {pre}_nombre
                FROM {$this->{recurso}Table}
                WHERE {pre}_id = :id
                LIMIT 1";
        $row = $this->db->query($sql, ['id' => $id])->fetch();
        return is_array($row) ? $row : null;
    }

    // -------------------------------------------------------------------------
    // CRUD con nombres semánticos
    // -------------------------------------------------------------------------
    public function createRecord(array $data): string
    {
        return $this->create([
            '{pre}_nombre' => (string) ($data['{pre}_nombre'] ?? ''),
            // Agregar más campos aquí
        ]);
    }

    public function updateRecord(string $id, array $data): bool
    {
        return $this->update($id, [
            '{pre}_nombre' => (string) ($data['{pre}_nombre'] ?? ''),
        ]);
    }

    public function deleteRecord(string $id): bool
    {
        return $this->delete($id);
    }

    // -------------------------------------------------------------------------
    // Validación de unicidad
    // -------------------------------------------------------------------------
    public function existsByName(string $nombre, ?string $excludeId = null): bool
    {
        return $this->existsIn(
            $this->{recurso}Table,
            '{pre}_nombre',
            trim($nombre),
            '{pre}_id',
            $excludeId
        );
    }

    // -------------------------------------------------------------------------
    // Opcional: verificación de FK activa
    // -------------------------------------------------------------------------
    // public function isLinkedTo{OtroModulo}(string $id): bool
    // {
    //     return $this->linkedTo($this->tableName('{otra_tabla}'), '{fk_col}', $id);
    // }
}
```

---

## 3. Vista: index — `app/Views/{recurso}/index.php`

```php
<div class="container-fluid">
    <?php
    $groupId    = trim((string) (($user['group'] ?? '')));
    $canAgregar = false;
    $canEditar  = false;
    $canEliminar = false;

    if ($groupId !== '') {
        try {
            $permission  = new \Core\Permission();
            $canAgregar  = $permission->canAccessRoute($groupId, '/{recursos}/agregar', 'agregar') === true;
            $canEditar   = $permission->canAccessRoute($groupId, '/{recursos}/1/editar', 'editar') === true;
            $canEliminar = $permission->canAccessRoute($groupId, '/{recursos}/1/eliminar', 'eliminar') === true;
        } catch (\Throwable) {}
    }
    ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5>{TituloPlural}</h5>
                        <span>Listado de {tituloPlural}</span>
                    </div>
                    <?php if ($canAgregar): ?>
                        <a href="/{recursos}/agregar" class="btn btn-primary btn-sm">
                            <span>Agregar</span>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body pt-3">
                    <?php
                    $searchView = dirname(__DIR__) . '/components/search-form.php';
                    if (is_file($searchView)) { include $searchView; }
                    $flashView = dirname(__DIR__) . '/components/flash-messages.php';
                    if (is_file($flashView)) { include $flashView; }
                    ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th class="text-center" style="width:150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty(${recurso}s)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">
                                        <?= !empty($search) ? 'No se encontraron resultados.' : 'No hay registros.' ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (${recurso}s as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) ($item['{pre}_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($item['{pre}_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="text-center">
                                            <?php $itemId = urlencode((string) ($item['{pre}_id'] ?? '')); ?>
                                            <?php if ($canEditar): ?>
                                                <a href="/{recursos}/<?= $itemId ?>/editar" class="btn btn-warning px-2 py-1" title="Editar">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($canEliminar): ?>
                                                <a href="/{recursos}/<?= $itemId ?>/eliminar" class="btn btn-danger px-2 py-1" title="Eliminar">
                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!$canEditar && !$canEliminar): ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    $paginationAriaLabel = 'Paginacion {recurso}s';
                    $paginationClass     = 'pagination justify-content-center pagination-primary pagination-sm';
                    $paginationView      = dirname(__DIR__) . '/components/pagination.php';
                    if (is_file($paginationView)) { include $paginationView; }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 4. Vista: agregar — `app/Views/{recurso}/agregar.php`

```php
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Nuevo {titulo}</h5><span>Agregar registro</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) { include $errorView; }
                    ?>
                    <form method="post" action="/{recursos}/guardar">
                        <?= $csrfField ?>
                        <div class="mb-3">
                            <label for="{pre}_nombre" class="form-label">Nombre</label>
                            <input id="{pre}_nombre" class="form-control" type="text"
                                   name="{pre}_nombre"
                                   value="<?= htmlspecialchars((string) ($form['{pre}_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <!-- Agregar más campos según el modelo -->
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="/{recursos}" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 5. Vista: editar — `app/Views/{recurso}/editar.php`

```php
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Editar {titulo}</h5><span>Modificar registro</span>
                </div>
                <div class="card-body">
                    <?php
                    $errorView = dirname(__DIR__) . '/components/form-errors.php';
                    if (is_file($errorView)) { include $errorView; }
                    ?>
                    <form method="post" action="/{recursos}/<?= urlencode((string) ${recurso}Id) ?>/actualizar">
                        <?= $csrfField ?>
                        <div class="mb-3">
                            <label for="{pre}_nombre" class="form-label">Nombre</label>
                            <input id="{pre}_nombre" class="form-control" type="text"
                                   name="{pre}_nombre"
                                   value="<?= htmlspecialchars((string) ($form['{pre}_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <!-- Agregar más campos según el modelo -->
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <a href="/{recursos}" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 6. Vista: eliminar — `app/Views/{recurso}/eliminar.php`

```php
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Eliminar {titulo}</h5>
                </div>
                <div class="card-body">
                    <?php
                    $flashView = dirname(__DIR__) . '/components/flash-messages.php';
                    if (is_file($flashView)) { include $flashView; }
                    ?>
                    <div class="alert alert-warning">
                        ¿Está seguro que desea eliminar
                        <strong><?= htmlspecialchars((string) ($form['{pre}_nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>?
                        Esta acción no se puede deshacer.
                    </div>
                    <form method="post" action="/{recursos}/<?= urlencode((string) ${recurso}Id) ?>/borrar">
                        <?= $csrfField ?>
                        <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                        <a href="/{recursos}" class="btn btn-light">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## 7. Migración — `core/database/migrations/NNNN_create_{tabla}.sql`

```sql
CREATE TABLE IF NOT EXISTS `{prefix}{tabla}` (
    `{pre}_id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `{pre}_nombre`     VARCHAR(250)     NOT NULL,
    `{pre}_estado`     CHAR(1)          NOT NULL DEFAULT 'A'
                           COMMENT 'A=Activo, I=Inactivo',
    `{pre}_created_at` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `{pre}_updated_at` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP
                           ON UPDATE CURRENT_TIMESTAMP,
    -- Agregar más columnas según el módulo
    PRIMARY KEY (`{pre}_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Migración DOWN — `NNNN_create_{tabla}_down.sql`

```sql
DROP TABLE IF EXISTS `{prefix}{tabla}`;
```

---

## 8. Rutas — agregar en `routes/web.php`

```php
// {TituloPlural}
$router->get('/{recursos}',                      {Modulo}Controller::class, 'index');
$router->get('/{recursos}/agregar',              {Modulo}Controller::class, 'agregar');
$router->post('/{recursos}/guardar',             {Modulo}Controller::class, 'guardar');
$router->get('/{recursos}/{id}/editar',          {Modulo}Controller::class, 'editar');
$router->post('/{recursos}/{id}/actualizar',     {Modulo}Controller::class, 'actualizar');
$router->get('/{recursos}/{id}/eliminar',        {Modulo}Controller::class, 'eliminar');
$router->post('/{recursos}/{id}/borrar',         {Modulo}Controller::class, 'borrar');
```

---

## Checklist de creación de módulo

- [ ] Crear `app/Controllers/{Modulo}Controller.php`
- [ ] Crear `app/Models/{Modulo}Model.php`
- [ ] Crear `app/Views/{recurso}/index.php`
- [ ] Crear `app/Views/{recurso}/agregar.php`
- [ ] Crear `app/Views/{recurso}/editar.php`
- [ ] Crear `app/Views/{recurso}/eliminar.php`
- [ ] Crear `core/database/migrations/NNNN_create_{tabla}.sql`
- [ ] Crear `core/database/migrations/NNNN_create_{tabla}_down.sql`
- [ ] Agregar 7 rutas en `routes/web.php`
- [ ] Ejecutar `php migrate.php`
- [ ] Registrar el elemento en la tabla `elemento` (para el menú)
- [ ] Asignar permisos al grupo en la tabla `permiso`

---

## Variables de sustitución — tabla de referencia rápida

| Placeholder | Descripción | Ejemplo |
|---|---|---|
| `{Modulo}` | Nombre PascalCase singular | `Producto` |
| `{recurso}` | Nombre minúsculas singular | `producto` |
| `{recursos}` | Nombre minúsculas plural (URL) | `productos` |
| `{pre}` | Prefijo de 3 letras para columnas | `pro` |
| `{tabla}` | Nombre de tabla sin prefijo DB | `producto` |
| `{titulo}` | Etiqueta singular para UI | `producto` |
| `{TituloSingular}` | Etiqueta singular capitalizada | `Producto` |
| `{TituloPlural}` | Etiqueta plural capitalizada | `Productos` |
| `{tituloPlural}` | Etiqueta plural minúscula | `productos` |
